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

<body>
    <main id="main-content" class="ml-64 p-3 bg-slate-100 min-h-screen transition-all duration-300">
        <!--Toggle button-->
        <button id="toggle-btn" class="flex flex-col gap-1 p-5 hover:bg-slate-200 rounded transition-colors">
            <span class="w-6 h-0.5 bg-slate-800"></span>
            <span class="w-6 h-0.5 bg-slate-800"></span>
            <span class="w-6 h-0.5 bg-slate-800"></span>
        </button>
        <!-- Page content-->
        <div>
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-5">
                <!-- Card 1 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                    <p class="text-sm font-medium text-slate-500">Total Products</p>
                    <h3 class="text-2xl font-bold text-slate-800">1,284</h3>
                </div>
                <!-- Card 2 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                    <p class="text-sm font-medium text-slate-500">Total Revenue</p>
                    <h3 class="text-2xl font-bold text-slate-800">$12,450</h3>
                </div>
                <!-- Card 3 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                    <p class="text-sm font-medium text-slate-500">Total Sales</p>
                    <h3 class="text-2xl font-bold text-blue-600">$12,450</h3>
                </div>
                <!-- Card 4 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                    <p class="text-sm font-medium text-slate-500">Orders Today</p>
                    <h3 class="text-2xl font-bold text-slate-800">7</h3>
                </div>
                <!-- Card 5 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                    <p class="text-sm font-medium text-slate-500">Low Stock Items</p>
                    <h3 class="text-2xl font-bold text-red-500">12</h3>
                </div>
                <!-- Card 6 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                    <p class="text-sm font-medium text-slate-500">Out of Stock</p>
                    <h3 class="text-2xl font-bold text-slate-800">4</h3>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mb-5">
            <!-- Sales Performance Section -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800">Sales Performance</h3>
                </div>
                <div class="p-6">
                    <p class="text-slate-400 italic">No recent sales data to display yet.</p>
                </div>
            </div>

            <!-- Recent Transactions Table Section -->
            <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800">Recent Transactions</h3>
                </div>
                <div class="p-6">
                    <p class="text-slate-400 italic">No recent transactions to display yet.</p>
                </div>
            </div>
        </div>
        <!-- Stock Alerts and Recommendations Table Section -->
        <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-800">Stock Alerts and Recommendations</h3>
            </div>
            <div class="p-6">
                <p class="text-slate-400 italic">No recent alerts to display yet.</p>
            </div>
        </div>
    </main>
    <script src="assets/js/script.js"></script>
</body>

</html>