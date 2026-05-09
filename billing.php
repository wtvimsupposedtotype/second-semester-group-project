<?php include 'includes/db.php';
?>
<?php include 'includes/sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIBS - Dashboard</title>
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
            <div class="w-2/3 p-6 overflow-y-auto">
                <div class="mb-6">
                    <input type="text" id="product-search" placeholder="Search by name or scan barcode..."
                        class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 shadow-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4" id="product-grid">
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 hover:border-blue-400 cursor-pointer transition-all group">
                        <div class="h-32 bg-slate-50 rounded-lg mb-3 flex items-center justify-center text-slate-300">
                            <span class="text-xs uppercase font-bold">No Image</span>
                        </div>
                        <h3 class="font-bold text-slate-800">Standard Mouse</h3>
                        <p class="text-blue-600 font-bold">1,500 LKR</p>
                        <p class="text-xs text-slate-400 mt-1">Stock: 45</p>
                    </div>
                </div>
            </div>

            <div class="w-1/3 bg-white border-l border-slate-200 flex flex-col">

                <div class="flex justify-between items-center">
                    <div class="p-4 border-b border-slate-100">
                        <h2 class="text-lg font-bold text-slate-800">Current Order</h2>
                    </div>
                    <p class="text-slate-500 font-medium" id="current-time">06:20 PM</p>
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-4" id="cart-items">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-medium text-slate-800">Standard Mouse</p>
                            <p class="text-xs text-slate-500">1,500 x 1</p>
                        </div>
                        <p class="font-bold text-slate-800">1,500</p>
                    </div>
                </div>

                <div class="p-6 bg-slate-50 border-t border-slate-200 space-y-3">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal</span>
                        <span>1,500.00</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Tax (0%)</span>
                        <span>0.00</span>
                    </div>
                    <div class="flex justify-between text-xl font-bold text-slate-900 pt-3 border-t border-slate-200">
                        <span>Total</span>
                        <span>1,500.00 LKR</span>
                    </div>
                    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-xl font-bold mt-4 shadow-lg shadow-blue-200 transition-all">
                        COMPLETE SALE
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/script.js"></script>
</body>