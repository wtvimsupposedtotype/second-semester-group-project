<?php
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/settings.php';
require_login(); // must be logged in to view inventory

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
                    <form action="actions/product_actions.php" method="POST"
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
                                    <td class="p-4 text-slate-800 font-medium"><?php echo htmlspecialchars($p['name']); ?></td>
                                    <td class="p-4 text-slate-500 text-sm"><?php echo htmlspecialchars($p['sku'] ?? '—'); ?></td>
                                    <td class="p-4 text-slate-500 text-sm"><?php echo htmlspecialchars($p['category'] ?? '—'); ?></td>
                                    <td class="p-4 text-slate-800"><?php echo number_format($p['price'], 2); ?></td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 <?php echo $badge; ?> rounded-md text-xs font-bold">
                                            <?php echo htmlspecialchars($label); ?>
                                        </span>
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
</body>

</html>
