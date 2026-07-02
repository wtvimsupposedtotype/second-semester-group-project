<?php
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/settings.php';
require_login(); // must be logged in to use the POS

/**
 * --- Invoice view ---
 * Visiting billing.php?invoice=SALE_ID renders a printable invoice page
 * and stops, instead of the POS page. No external PDF library needed:
 * the page auto-opens the browser's print dialog, and choosing
 * "Save as PDF" as the destination produces a real PDF.
 */
if (isset($_GET['invoice'])) {
    $sale_id = (int) $_GET['invoice'];
    if ($sale_id <= 0) {
        http_response_code(400);
        die('Invalid invoice request.');
    }

    // Fetch the sale
    $stmt = $conn->prepare("SELECT * FROM sales WHERE id = ?");
    $stmt->bind_param("i", $sale_id);
    $stmt->execute();
    $sale = $stmt->get_result()->fetch_assoc();

    if (!$sale) {
        http_response_code(404);
        die('Invoice not found.');
    }

    // Fetch the line items for this sale.
    // Adjust table/column names here if your schema differs.
    $invoice_items = [];
    $istmt = $conn->prepare("SELECT si.quantity, si.price_each AS price, p.name
                              FROM sale_items si
                              LEFT JOIN products p ON si.product_id = p.id
                              WHERE si.sale_id = ?");
    $istmt->bind_param("i", $sale_id);
    $istmt->execute();
    $ires = $istmt->get_result();
    while ($row = $ires->fetch_assoc()) {
        $invoice_items[] = $row;
    }

    // Business info from Settings
    $inv_store_name = get_setting($conn, 'store_name', 'My Store');
    $inv_address    = get_setting($conn, 'address', '');
    $inv_currency   = get_setting($conn, 'currency', 'LKR');

    $inv_subtotal   = (float) $sale['total_amount'] - (float) $sale['tax_amount'];
    $inv_tax        = (float) $sale['tax_amount'];
    $inv_total      = (float) $sale['total_amount'];
    $inv_date       = isset($sale['sale_date']) ? date('M d, Y h:i A', strtotime($sale['sale_date'])) : date('M d, Y h:i A');
    $inv_invoice_no = 'INV-' . str_pad((string) $sale_id, 6, '0', STR_PAD_LEFT);
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Invoice <?php echo htmlspecialchars($inv_invoice_no); ?></title>
        <style>
            * {
                box-sizing: border-box;
            }

            body {
                font-family: Helvetica, Arial, sans-serif;
                color: #1e293b;
                background: #e2e8f0;
                margin: 0;
                padding: 24px;
            }

            .sheet {
                background: #fff;
                max-width: 720px;
                margin: 0 auto;
                padding: 40px;
                border-radius: 8px;
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
            }

            .toolbar {
                max-width: 720px;
                margin: 0 auto 16px;
                display: flex;
                justify-content: flex-end;
                gap: 10px;
            }

            .toolbar button {
                background: #2563eb;
                color: #fff;
                border: none;
                padding: 10px 18px;
                border-radius: 8px;
                font-weight: bold;
                cursor: pointer;
                font-size: 14px;
            }

            .toolbar button:hover {
                background: #1d4ed8;
            }

            .toolbar button.secondary {
                background: #e2e8f0;
                color: #475569;
            }

            .toolbar button.secondary:hover {
                background: #cbd5e1;
            }

            .header {
                border-bottom: 1px solid #e2e8f0;
                padding-bottom: 16px;
                margin-bottom: 24px;
            }

            .store-name {
                font-size: 22px;
                font-weight: bold;
                margin: 0;
            }

            .store-address {
                font-size: 12px;
                color: #64748b;
                margin: 4px 0 0;
            }

            .meta {
                display: flex;
                justify-content: space-between;
                margin-bottom: 24px;
            }

            .meta .title {
                font-size: 18px;
                font-weight: bold;
            }

            .meta .right {
                text-align: right;
                color: #475569;
                font-size: 13px;
            }

            table.items {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 24px;
            }

            table.items th {
                background: #f1f5f9;
                color: #334155;
                text-align: left;
                padding: 10px;
                font-size: 12px;
                border: 1px solid #e2e8f0;
            }

            table.items td {
                padding: 9px 10px;
                border: 1px solid #e2e8f0;
                font-size: 13px;
            }

            table.items td.center {
                text-align: center;
            }

            table.items td.right {
                text-align: right;
            }

            table.items td.empty {
                text-align: center;
                color: #94a3b8;
                font-style: italic;
            }

            .totals {
                width: 100%;
                margin-top: 6px;
            }

            .totals td {
                padding: 6px 8px;
                font-size: 13px;
            }

            .totals .label {
                text-align: right;
                color: #475569;
            }

            .totals .value {
                text-align: right;
                width: 120px;
            }

            .totals .grand td {
                border-top: 1px solid #e2e8f0;
                font-weight: bold;
                font-size: 16px;
                color: #1e293b;
                padding-top: 10px;
            }

            .footer {
                margin-top: 48px;
                text-align: center;
                color: #94a3b8;
                font-size: 11px;
                font-style: italic;
            }

            @media print {
                body {
                    background: #fff;
                    padding: 0;
                }

                .toolbar {
                    display: none;
                }

                .sheet {
                    box-shadow: none;
                    border-radius: 0;
                    max-width: 100%;
                    padding: 0;
                }
            }
        </style>
    </head>

    <body>

        <div class="toolbar">
            <button class="secondary" onclick="window.close();">Close</button>
            <button onclick="window.print();">🖨 Print / Save as PDF</button>
        </div>

        <div class="sheet">
            <div class="header">
                <p class="store-name"><?php echo htmlspecialchars($inv_store_name); ?></p>
                <p class="store-address"><?php echo htmlspecialchars($inv_address); ?></p>
            </div>

            <div class="meta">
                <div class="title">INVOICE</div>
                <div class="right">
                    Invoice #: <?php echo htmlspecialchars($inv_invoice_no); ?><br>
                    Date: <?php echo htmlspecialchars($inv_date); ?>
                </div>
            </div>

            <table class="items">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="center">Qty</th>
                        <th class="right">Unit Price</th>
                        <th class="right">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($invoice_items) === 0): ?>
                        <tr>
                            <td colspan="4" class="empty">No line item details available for this sale.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoice_items as $it): ?>
                            <?php
                            $name      = $it['name'] ?? 'Unknown item';
                            $qty       = (int) $it['quantity'];
                            $price     = (float) $it['price'];
                            $lineTotal = $qty * $price;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($name); ?></td>
                                <td class="center"><?php echo $qty; ?></td>
                                <td class="right"><?php echo number_format($price, 2); ?></td>
                                <td class="right"><?php echo number_format($lineTotal, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <table class="totals">
                <tr>
                    <td class="label">Subtotal</td>
                    <td class="value"><?php echo htmlspecialchars($inv_currency); ?> <?php echo number_format($inv_subtotal, 2); ?></td>
                </tr>
                <tr>
                    <td class="label">Tax</td>
                    <td class="value"><?php echo htmlspecialchars($inv_currency); ?> <?php echo number_format($inv_tax, 2); ?></td>
                </tr>
                <tr class="grand">
                    <td class="label">Total</td>
                    <td class="value"><?php echo htmlspecialchars($inv_currency); ?> <?php echo number_format($inv_total, 2); ?></td>
                </tr>
            </table>

            <div class="footer">Thank you for your business!</div>
        </div>

        <script>
            window.addEventListener('load', function() {
                setTimeout(function() {
                    window.print();
                }, 300);
            });
        </script>
    </body>

    </html>
<?php
    exit;
}

$tax_rate = (float) get_setting($conn, 'tax_rate', '0'); // percent

// Load all products for the grid (out-of-stock ones show but can't be added)
$products = $conn->query("SELECT id, name, sku, price, quantity FROM products ORDER BY name ASC");
?>
<?php include 'includes/sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIBS - Point of Sale</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-50">

    <main id="main-content" class="ml-64 min-h-screen transition-all duration-300 flex flex-col">
        <header class="bg-white border-b border-slate-200 p-4 flex justify-between items-center sticky top-0 z-10">
            <div class="flex items-center gap-4">
                <button id="toggle-btn" class="p-2 hover:bg-slate-100 rounded-lg">
                    <span class="block w-6 h-0.5 bg-slate-600 mb-1"></span>
                    <span class="block w-6 h-0.5 bg-slate-600 mb-1"></span>
                    <span class="block w-6 h-0.5 bg-slate-600"></span>
                </button>
                <h1 class="text-xl font-bold text-slate-800">Point of Sale</h1>
            </div>
        </header>

        <div class="flex-1 flex overflow-hidden">
            <!-- LEFT: product picker -->
            <div class="w-2/3 p-6 overflow-y-auto">
                <div class="mb-6">
                    <input type="text" id="product-search" onkeyup="filterProducts()"
                        placeholder="Search products by name..."
                        class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 shadow-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4" id="product-grid">
                    <?php if ($products->num_rows === 0): ?>
                        <p class="text-slate-400 italic col-span-full">No products yet. Add some on the Inventory page first.</p>
                    <?php else: ?>
                        <?php while ($p = $products->fetch_assoc()):
                            $out = ((int) $p['quantity']) <= 0; ?>
                            <div class="product-card bg-white p-4 rounded-xl shadow-sm border border-slate-100 transition-all <?php echo $out ? 'opacity-50 cursor-not-allowed' : 'hover:border-blue-400 cursor-pointer'; ?>"
                                data-id="<?php echo (int) $p['id']; ?>"
                                data-name="<?php echo htmlspecialchars($p['name'], ENT_QUOTES); ?>"
                                data-price="<?php echo (float) $p['price']; ?>"
                                data-stock="<?php echo (int) $p['quantity']; ?>">
                                <div class="h-32 bg-slate-50 rounded-lg mb-3 flex items-center justify-center text-slate-300">
                                    <span class="text-xs uppercase font-bold">No Image</span>
                                </div>
                                <h3 class="font-bold text-slate-800"><?php echo htmlspecialchars($p['name']); ?></h3>
                                <p class="text-blue-600 font-bold"><?php echo number_format($p['price'], 2); ?> LKR</p>
                                <p class="text-xs <?php echo $out ? 'text-red-500' : 'text-slate-400'; ?> mt-1">
                                    <?php echo $out ? 'Out of stock' : 'Stock: ' . (int) $p['quantity']; ?>
                                </p>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RIGHT: current order / cart -->
            <div class="w-1/3 bg-white border-l border-slate-200 flex flex-col">
                <div class="flex justify-between items-center">
                    <div class="p-4 border-b border-slate-100">
                        <h2 class="text-lg font-bold text-slate-800">Current Order</h2>
                    </div>
                    <p class="text-slate-500 font-medium pr-4" id="current-time">--:--</p>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-4" id="cart-items">
                    <p class="text-slate-400 text-sm text-center mt-8">Cart is empty. Click a product to add it.</p>
                </div>

                <div class="p-6 bg-slate-50 border-t border-slate-200 space-y-3">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal</span>
                        <span id="subtotal">0.00</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Tax (<?php echo (float) $tax_rate; ?>%)</span>
                        <span id="tax">0.00</span>
                    </div>
                    <div class="flex justify-between text-xl font-bold text-slate-900 pt-3 border-t border-slate-200">
                        <span>Total</span>
                        <span id="total">0.00 LKR</span>
                    </div>
                    <button id="complete-sale" onclick="completeSale()"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-xl font-bold mt-4 shadow-lg shadow-blue-200 transition-all">
                        COMPLETE SALE
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script>
        // ---- Cart state: { productId: {id, name, price, stock, qty} } ----
        const cart = {};
        const TAX_RATE = <?php echo (float) $tax_rate; ?>; // percent, from Settings

        function escapeHtml(s) {
            const d = document.createElement('div');
            d.textContent = s;
            return d.innerHTML;
        }

        // Add a product when its card is clicked (event delegation on the grid)
        document.getElementById('product-grid').addEventListener('click', function(e) {
            const card = e.target.closest('.product-card');
            if (!card) return;
            const stock = parseInt(card.dataset.stock);
            if (stock <= 0) return; // out of stock, ignore
            addToCart(parseInt(card.dataset.id), card.dataset.name, parseFloat(card.dataset.price), stock);
        });

        function addToCart(id, name, price, stock) {
            if (!cart[id]) cart[id] = {
                id,
                name,
                price,
                stock,
                qty: 0
            };
            if (cart[id].qty >= stock) {
                alert('No more stock available for ' + name + '.');
                return;
            }
            cart[id].qty++;
            renderCart();
        }

        function changeQty(id, delta) {
            if (!cart[id]) return;
            cart[id].qty += delta;
            if (cart[id].qty <= 0) {
                delete cart[id];
            } else if (cart[id].qty > cart[id].stock) {
                cart[id].qty = cart[id].stock;
                alert('Only ' + cart[id].stock + ' in stock.');
            }
            renderCart();
        }

        function removeItem(id) {
            delete cart[id];
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cart-items');
            const ids = Object.keys(cart);

            if (ids.length === 0) {
                container.innerHTML = '<p class="text-slate-400 text-sm text-center mt-8">Cart is empty. Click a product to add it.</p>';
                updateTotals();
                return;
            }

            let html = '';
            ids.forEach(id => {
                const it = cart[id];
                const lineTotal = it.price * it.qty;
                html += `
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-medium text-slate-800">${escapeHtml(it.name)}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <button onclick="changeQty(${it.id},-1)" class="w-6 h-6 rounded bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold leading-none">−</button>
                            <span class="text-sm w-6 text-center">${it.qty}</span>
                            <button onclick="changeQty(${it.id},1)" class="w-6 h-6 rounded bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold leading-none">+</button>
                            <span class="text-xs text-slate-400 ml-1">@ ${it.price.toFixed(2)}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-slate-800">${lineTotal.toFixed(2)}</p>
                        <button onclick="removeItem(${it.id})" class="text-xs text-red-500 hover:underline">remove</button>
                    </div>
                </div>`;
            });
            container.innerHTML = html;
            updateTotals();
        }

        function updateTotals() {
            let subtotal = 0;
            Object.values(cart).forEach(it => subtotal += it.price * it.qty);
            const tax = subtotal * TAX_RATE / 100;
            const total = subtotal + tax;
            document.getElementById('subtotal').textContent = subtotal.toFixed(2);
            document.getElementById('tax').textContent = tax.toFixed(2);
            document.getElementById('total').textContent = total.toFixed(2) + ' LKR';
        }

        function completeSale() {
            const items = Object.values(cart).map(it => ({
                id: it.id,
                qty: it.qty
            }));
            if (items.length === 0) {
                alert('Cart is empty.');
                return;
            }

            const btn = document.getElementById('complete-sale');
            btn.disabled = true;
            btn.textContent = 'Processing...';

            fetch('actions/complete_sale.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        items
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showInvoiceSuccess(data);
                    } else {
                        alert('⚠️ ' + (data.error || 'Sale failed.'));
                        btn.disabled = false;
                        btn.textContent = 'COMPLETE SALE';
                    }
                })
                .catch(err => {
                    alert('Network error: ' + err);
                    btn.disabled = false;
                    btn.textContent = 'COMPLETE SALE';
                });
        }

        // Shows a success overlay with a "Download Invoice" button, then
        // refreshes the page (for updated stock numbers) once dismissed.
        function showInvoiceSuccess(data) {
            const overlay = document.createElement('div');
            overlay.className = 'fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50';
            overlay.innerHTML = `
                <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-sm text-center">
                    <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center mx-auto mb-4 text-2xl">✔</div>
                    <h2 class="text-lg font-bold text-slate-800 mb-1">Sale #${data.sale_id} completed</h2>
                    <p class="text-sm text-slate-400 mb-6">Total charged: <span class="font-semibold text-slate-700">${data.total} LKR</span></p>

                    <div class="text-left bg-slate-50 rounded-xl p-4 mb-6 text-sm text-slate-600 space-y-1">
                        <div class="flex justify-between"><span>Subtotal</span><span>${data.subtotal}</span></div>
                        <div class="flex justify-between"><span>Tax</span><span>${data.tax}</span></div>
                        <div class="flex justify-between font-bold text-slate-800 pt-1 border-t border-slate-200 mt-1"><span>Total</span><span>${data.total} LKR</span></div>
                    </div>

                    <a href="billing.php?invoice=${data.sale_id}" target="_blank"
                       class="block w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold mb-3 transition-colors">
                        ⬇ Download Invoice (PDF)
                    </a>
                    <button id="invoice-close-btn"
                            class="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 py-3 rounded-xl font-medium transition-colors">
                        New Sale
                    </button>
                </div>`;
            document.body.appendChild(overlay);

            document.getElementById('invoice-close-btn').addEventListener('click', () => {
                location.reload(); // refresh stock numbers
            });
        }

        function filterProducts() {
            const term = document.getElementById('product-search').value.toLowerCase();
            document.querySelectorAll('.product-card').forEach(card => {
                const name = card.dataset.name.toLowerCase();
                card.style.display = name.includes(term) ? '' : 'none';
            });
        }

        // Simple live clock for the order panel
        function tick() {
            const now = new Date();
            document.getElementById('current-time').textContent =
                now.toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });
        }
        tick();
        setInterval(tick, 1000);
    </script>
</body>

</html>