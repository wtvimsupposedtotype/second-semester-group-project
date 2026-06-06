<?php
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/settings.php';
require_login(); // must be logged in to use the POS

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
        document.getElementById('product-grid').addEventListener('click', function (e) {
            const card = e.target.closest('.product-card');
            if (!card) return;
            const stock = parseInt(card.dataset.stock);
            if (stock <= 0) return; // out of stock, ignore
            addToCart(parseInt(card.dataset.id), card.dataset.name, parseFloat(card.dataset.price), stock);
        });

        function addToCart(id, name, price, stock) {
            if (!cart[id]) cart[id] = { id, name, price, stock, qty: 0 };
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
            const items = Object.values(cart).map(it => ({ id: it.id, qty: it.qty }));
            if (items.length === 0) {
                alert('Cart is empty.');
                return;
            }

            const btn = document.getElementById('complete-sale');
            btn.disabled = true;
            btn.textContent = 'Processing...';

            fetch('actions/complete_sale.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ items })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Sale #' + data.sale_id + ' completed!\n'
                            + 'Subtotal: Rs ' + data.subtotal + '\n'
                            + 'Tax: Rs ' + data.tax + '\n'
                            + 'Total: Rs ' + data.total);
                        location.reload(); // refresh stock numbers
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
                now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
        tick();
        setInterval(tick, 1000);
    </script>
</body>

</html>
