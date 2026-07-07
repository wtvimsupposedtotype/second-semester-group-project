<?php
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/settings.php';
require_login(); // must be logged in to view inventory

/*
  --- WhatsApp alert sender (AJAX) ---
  The "Notify" buttons on this page POST to inventory.php?send_whatsapp=1
  with a JSON body, and this sends via Twilio's REST API (no SDK/Composer
  needed, just PHP's built-in curl), then returns JSON and stops before
  any HTML renders.
 
  Two ways it can send, chosen automatically:
    1. Plain text message (default) — works immediately in the sandbox.
    2. Content Template — used only if you've set a Template SID on the
       Settings page. Templates are pre-approved layouts you design once
       in Twilio Console -> Content Editor, with numbered placeholders
       like {{1}}, {{2}}, {{3}}. This code maps:
           {{1}} -> product name
           {{2}} -> quantity remaining
           {{3}} -> reorder threshold
       If your template uses different placeholders/order, adjust the
       $content_variables array below to match.
 
  One-time setup: sign up free at https://www.twilio.com/try-twilio,
  get a WhatsApp Sandbox number + join code from Console -> Messaging ->
  Try it out -> Send a WhatsApp message, join it from your WhatsApp, then
  enter your Account SID / Auth Token / numbers on the Settings page.
 */
if (isset($_GET['send_whatsapp'])) {
    header('Content-Type: application/json');

    $input        = json_decode(file_get_contents('php://input'), true);
    $message      = trim($input['message'] ?? '');
    $product_name = $input['product_name'] ?? null;
    $quantity     = $input['quantity'] ?? null;
    $threshold    = $input['threshold'] ?? null;

    if ($message === '' && $product_name === null) {
        echo json_encode(['success' => false, 'error' => 'No message provided.']);
        exit;
    }

    // --- Twilio credentials ---
    // Preferred source: the Settings page (stored in the settings table), so
    // staff can configure WhatsApp without touching code. If a value is left
    // blank in Settings, we fall back to includes/twilio_config.php (gitignored)
    // when it exists — handy for a default sandbox setup shared by the team.
    $twilio = [];
    $twilio_config_path = __DIR__ . '/includes/twilio_config.php';
    if (file_exists($twilio_config_path)) {
        $twilio = require $twilio_config_path;
    }

    // Settings values win; config file fills any gaps.
    $account_sid = get_setting($conn, 'twilio_sid', '')          ?: ($twilio['account_sid'] ?? '');
    $auth_token  = get_setting($conn, 'twilio_token', '')        ?: ($twilio['auth_token']  ?? '');
    $from_number = get_setting($conn, 'twilio_whatsapp_from', '') ?: ($twilio['from_number'] ?? '');
    $to_number   = get_setting($conn, 'notify_phone', '')        ?: ($twilio['to_number']   ?? '');
    $content_sid = $twilio['content_sid'] ?? ''; // optional Content Template SID

    if (empty($account_sid) || empty($auth_token) || empty($from_number) || empty($to_number)) {
        echo json_encode(['success' => false, 'error' => 'WhatsApp/Twilio is not configured yet. Set it up on the Settings page.']);
        exit;
    }

    $url = "https://api.twilio.com/2010-04-01/Accounts/{$account_sid}/Messages.json";

    // Twilio expects numbers prefixed with "whatsapp:+", e.g. whatsapp:+94771234567
    $from = 'whatsapp:+' . ltrim($from_number, '+');
    $to   = 'whatsapp:+' . ltrim($to_number, '+');

    $fields = [
        'From' => $from,
        'To'   => $to,
    ];

    // Use the Content Template only if one is configured AND this is a
    // single-product alert (bulk "Notify All" always uses plain text,
    // since a template has fixed slots and can't list an arbitrary
    // number of items).
    if (!empty($content_sid) && $product_name !== null) {
        $content_variables = json_encode([
            '1' => (string) $product_name,
            '2' => (string) $quantity,
            '3' => (string) $threshold,
        ]);
        $fields['ContentSid']       = $content_sid;
        $fields['ContentVariables'] = $content_variables;
    } else {
        $fields['Body'] = $message;
    }

    $payload = http_build_query($fields);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_USERPWD, $account_sid . ':' . $auth_token);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response   = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curl_error) {
        echo json_encode(['success' => false, 'error' => 'Network error: ' . $curl_error]);
        exit;
    }

    $decoded = json_decode($response, true);

    if ($http_code >= 200 && $http_code < 300) {
        echo json_encode(['success' => true, 'error' => null]);
    } else {
        $error_message = $decoded['message'] ?? ('Twilio returned HTTP ' . $http_code);
        echo json_encode(['success' => false, 'error' => $error_message]);
    }
    exit;
}

// Default reorder point for NEW products comes from Settings
$default_threshold = get_setting($conn, 'low_stock_default', 10);

// --- Are we adding or editing? ---
$edit_product = null;
if (isset($_GET['edit_id'])) {
    $eid  = (int) $_GET['edit_id'];
    $es   = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $es->bind_param("i", $eid);
    $es->execute();
    $edit_product = $es->get_result()->fetch_assoc();
}
$show_form = $edit_product !== null || (isset($_GET['show']) && $_GET['show'] === 'add');

// --- Search + category filter (both optional) ---
$q   = trim($_GET['q'] ?? '');
$cat = trim($_GET['category'] ?? '');

$sql    = "SELECT * FROM products WHERE 1=1";
$params = [];
$types  = "";
if ($q !== '') {
    $sql      .= " AND name LIKE ?";
    $params[]  = "%$q%";
    $types    .= "s";
}
if ($cat !== '' && $cat !== 'All Categories') {
    $sql      .= " AND category = ?";
    $params[]  = $cat;
    $types    .= "s";
}
$sql .= " ORDER BY name ASC";

$stmt = $conn->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

// Distinct categories for the filter dropdown
$cat_result = $conn->query("SELECT DISTINCT category FROM products
                            WHERE category IS NOT NULL AND category <> ''
                            ORDER BY category");

// Low-stock summary, for the bulk "Notify All" button at the top of the page
$low_stock_list = [];
$lsq = $conn->query("SELECT name, quantity, low_stock_threshold FROM products
                     WHERE quantity <= low_stock_threshold
                     ORDER BY quantity ASC");
while ($row = $lsq->fetch_assoc()) {
    $low_stock_list[] = $row;
}
?>
<?php include 'includes/sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIBS - Inventory</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-50">

    <main id="main-content" class="ml-64 min-h-screen transition-all duration-300">
        <header class="bg-white border-b border-slate-200 p-4 flex justify-between items-center sticky top-0 z-10">
            <div class="flex items-center gap-4">
                <button id="toggle-btn" class="p-2 hover:bg-slate-100 rounded-lg">
                    <span class="block w-6 h-0.5 bg-slate-600 mb-1"></span>
                    <span class="block w-6 h-0.5 bg-slate-600 mb-1"></span>
                    <span class="block w-6 h-0.5 bg-slate-600"></span>
                </button>
                <h1 class="text-xl font-bold text-slate-800">Inventory Management</h1>
            </div>
        </header>

        <div class="p-8">

            <?php /* ---- Status messages ---- */ ?>
            <?php if (isset($_GET['msg'])): ?>
                <?php
                $messages = [
                    'added'   => ['✅ Product added.', 'bg-green-50 text-green-700 border-green-200'],
                    'updated' => ['✅ Product updated.', 'bg-green-50 text-green-700 border-green-200'],
                    'deleted' => ['🗑️ Product deleted.', 'bg-slate-100 text-slate-700 border-slate-200'],
                    'invalid' => ['⚠️ Please enter a name and valid numbers for price/quantity.', 'bg-amber-50 text-amber-700 border-amber-200'],
                    'dup'     => ['⚠️ That SKU is already used by another product.', 'bg-amber-50 text-amber-700 border-amber-200'],
                ];
                $m = $messages[$_GET['msg']] ?? null;
                ?>
                <?php if ($m): ?>
                    <div class="mb-4 px-4 py-3 rounded-lg border text-sm <?php echo $m[1]; ?>">
                        <?php echo $m[0]; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php /* ---- Add / Edit form (only shown when adding or editing) ---- */ ?>
            <?php if ($show_form): ?>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mb-6">
                    <h2 class="text-lg font-bold text-slate-800 mb-4">
                        <?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?>
                    </h2>
                    <form action="actions/product_actions.php" method="POST" enctype="multipart/form-data"
                        class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="hidden" name="op" value="<?php echo $edit_product ? 'update' : 'add'; ?>">
                        <?php if ($edit_product): ?>
                            <input type="hidden" name="id" value="<?php echo (int) $edit_product['id']; ?>">
                        <?php endif; ?>

                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Product Name *</label>
                            <input type="text" name="name" required
                                value="<?php echo htmlspecialchars($edit_product['name'] ?? ''); ?>"
                                class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">SKU</label>
                            <input type="text" name="sku"
                                value="<?php echo htmlspecialchars($edit_product['sku'] ?? ''); ?>"
                                class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Category</label>
                            <input type="text" name="category"
                                value="<?php echo htmlspecialchars($edit_product['category'] ?? ''); ?>"
                                class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Selling Price (LKR) *</label>
                            <input type="number" step="0.01" name="price" required
                                value="<?php echo htmlspecialchars($edit_product['price'] ?? ''); ?>"
                                class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Cost Price (LKR)</label>
                            <input type="number" step="0.01" name="cost_price"
                                value="<?php echo htmlspecialchars($edit_product['cost_price'] ?? '0'); ?>"
                                class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            <p class="text-xs text-slate-400 mt-1">What you pay for it — used to calculate profit.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Quantity in Stock *</label>
                            <input type="number" name="quantity" required
                                value="<?php echo htmlspecialchars($edit_product['quantity'] ?? ''); ?>"
                                class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Low Stock Alert At</label>
                            <input type="number" name="low_stock_threshold"
                                value="<?php echo htmlspecialchars($edit_product['low_stock_threshold'] ?? $default_threshold); ?>"
                                class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-600 mb-1">Product Picture</label>
                            <div class="flex items-center gap-4">
                                <?php if (!empty($edit_product['image']) && file_exists($edit_product['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($edit_product['image']); ?>"
                                        alt="Current picture"
                                        class="w-16 h-16 object-cover rounded-lg border border-slate-200">
                                <?php endif; ?>
                                <input type="file" name="image" accept="image/*"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:bg-blue-600 file:text-white file:cursor-pointer hover:file:bg-blue-700 focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <p class="text-xs text-slate-400 mt-1">JPG, PNG, GIF or WEBP, up to 2&nbsp;MB.<?php echo $edit_product ? ' Leave empty to keep the current picture.' : ''; ?></p>
                        </div>

                        <div class="md:col-span-2 flex gap-3 justify-end pt-2">
                            <a href="inventory.php"
                                class="px-4 py-2 rounded-lg font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">
                                Cancel
                            </a>
                            <button type="submit"
                                class="px-5 py-2 rounded-lg font-bold text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                                <?php echo $edit_product ? 'Save Changes' : 'Add Product'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <?php if (count($low_stock_list) > 0): ?>
                <?php
                $summary_lines = ["⚠️ Low Stock Report", ""];
                foreach ($low_stock_list as $row) {
                    $summary_lines[] = "- " . $row['name'] . ": " . (int) $row['quantity'] . " left (threshold " . (int) $row['low_stock_threshold'] . ")";
                }
                $summary_message = implode("\n", $summary_lines);
                ?>
                <div class="mb-6 px-4 py-3 rounded-lg border bg-amber-50 border-amber-200 flex items-center justify-between flex-wrap gap-3">
                    <span class="text-sm text-amber-700">⚠️ <?php echo count($low_stock_list); ?> product<?php echo count($low_stock_list) === 1 ? '' : 's'; ?> at or below reorder threshold.</span>
                    <button type="button" onclick="sendWhatsappAlert(this, <?php echo htmlspecialchars(json_encode($summary_message), ENT_QUOTES); ?>)"
                        class="notify-btn bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-4 py-2 rounded-lg font-medium transition-colors whitespace-nowrap">
                        📱 Notify All via WhatsApp
                    </button>
                </div>
            <?php endif; ?>

            <?php /* ---- Search / filter bar ---- */ ?>
            <form method="GET" action="inventory.php"
                class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 mb-6 flex gap-4">
                <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>"
                    placeholder="Search products..."
                    class="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="category" class="bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus:outline-none">
                    <option value="">All Categories</option>
                    <?php while ($c = $cat_result->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($c['category']); ?>"
                            <?php echo ($cat === $c['category']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['category']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Search
                </button>
                <a href="inventory.php?show=add"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors whitespace-nowrap">
                    + Add Product
                </a>
            </form>

            <?php /* ---- Product table ---- */ ?>
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="p-4 font-semibold text-slate-700">Product Name</th>
                            <th class="p-4 font-semibold text-slate-700">SKU</th>
                            <th class="p-4 font-semibold text-slate-700">Category</th>
                            <th class="p-4 font-semibold text-slate-700">Price (LKR)</th>
                            <th class="p-4 font-semibold text-slate-700">Stock</th>
                            <th class="p-4 font-semibold text-slate-700 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if ($products->num_rows === 0): ?>
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400 italic">
                                    No products found. Click “+ Add Product” to create your first one.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while ($p = $products->fetch_assoc()): ?>
                                <?php
                                $qty = (int) $p['quantity'];
                                $thr = (int) ($p['low_stock_threshold'] ?? 5);
                                if ($qty === 0) {
                                    $badge = 'bg-red-100 text-red-700';
                                    $label = 'Out of Stock';
                                } elseif ($qty <= $thr) {
                                    $badge = 'bg-amber-100 text-amber-700';
                                    $label = $qty . ' Low Stock';
                                } else {
                                    $badge = 'bg-green-100 text-green-700';
                                    $label = $qty . ' In Stock';
                                }
                                ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-4 text-slate-800 font-medium">
                                        <div class="flex items-center gap-3">
                                            <?php if (!empty($p['image']) && file_exists($p['image'])): ?>
                                                <img src="<?php echo htmlspecialchars($p['image']); ?>"
                                                    alt="<?php echo htmlspecialchars($p['name']); ?>"
                                                    class="w-10 h-10 object-cover rounded-md border border-slate-200 flex-shrink-0">
                                            <?php else: ?>
                                                <span class="w-10 h-10 rounded-md bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-300 flex-shrink-0">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                                    </svg>
                                                </span>
                                            <?php endif; ?>
                                            <span><?php echo htmlspecialchars($p['name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="p-4 text-slate-500 text-sm"><?php echo htmlspecialchars($p['sku'] ?? '—'); ?></td>
                                    <td class="p-4 text-slate-500 text-sm"><?php echo htmlspecialchars($p['category'] ?? '—'); ?></td>
                                    <td class="p-4 text-slate-800"><?php echo number_format($p['price'], 2); ?></td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 <?php echo $badge; ?> rounded-md text-xs font-bold">
                                            <?php echo htmlspecialchars($label); ?>
                                        </span>
                                        <?php if ($qty <= $thr): ?>
                                            <?php
                                            $row_message = ($qty === 0 ? "⚠️ OUT OF STOCK\n" : "⚠️ LOW STOCK\n")
                                                . "Product: " . $p['name'] . "\n"
                                                . "Remaining: " . $qty . " unit(s)\n"
                                                . "Reorder threshold: " . $thr;
                                            ?>
                                            <button type="button"
                                                onclick="sendWhatsappAlert(this, <?php echo htmlspecialchars(json_encode($row_message), ENT_QUOTES); ?>, <?php echo htmlspecialchars(json_encode($p['name']), ENT_QUOTES); ?>, <?php echo $qty; ?>, <?php echo $thr; ?>)"
                                                class="notify-btn ml-2 text-xs text-emerald-600 hover:underline whitespace-nowrap bg-transparent border-0 p-0 cursor-pointer">
                                                📱 Notify
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-center whitespace-nowrap">
                                        <a href="inventory.php?edit_id=<?php echo (int) $p['id']; ?>"
                                            class="text-blue-600 hover:underline mr-3">Edit</a>
                                        <form action="actions/product_actions.php" method="POST" class="inline"
                                            onsubmit="return confirm('Delete this product? This cannot be undone.');">
                                            <input type="hidden" name="op" value="delete">
                                            <input type="hidden" name="id" value="<?php echo (int) $p['id']; ?>">
                                            <button type="submit" class="text-red-500 hover:underline">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="assets/js/script.js"></script>
    <script>
        function sendWhatsappAlert(btn, message, productName, quantity, threshold) {
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Sending...';

            const body = {
                message
            };
            if (productName !== undefined) {
                body.product_name = productName;
                body.quantity = quantity;
                body.threshold = threshold;
            }

            fetch('inventory.php?send_whatsapp=1', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(body)
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        btn.textContent = '✔ Sent';
                        setTimeout(() => {
                            btn.disabled = false;
                            btn.textContent = originalText;
                        }, 3000);
                    } else {
                        alert('⚠️ Failed to send: ' + (data.error || 'Unknown error.'));
                        btn.disabled = false;
                        btn.textContent = originalText;
                    }
                })
                .catch(err => {
                    alert('Network error: ' + err);
                    btn.disabled = false;
                    btn.textContent = originalText;
                });
        }
    </script>
</body>

</html>