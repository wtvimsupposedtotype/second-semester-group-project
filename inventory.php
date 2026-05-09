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
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 mb-6 flex gap-4">
                <input type="text" placeholder="Search products..." class="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select class="bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus:outline-none">
                    <option>All Categories</option>
                    <option>Electronics</option>
                    <option>Groceries</option>
                </select>
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    + Add Product
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="p-4 font-semibold text-slate-700">Product Name</th>
                            <th class="p-4 font-semibold text-slate-700">SKU</th>
                            <th class="p-4 font-semibold text-slate-700">Price (LKR)</th>
                            <th class="p-4 font-semibold text-slate-700">Stock</th>
                            <th class="p-4 font-semibold text-slate-700 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4 text-slate-800 font-medium">Standard Mouse</td>
                            <td class="p-4 text-slate-500 text-sm">EL-001</td>
                            <td class="p-4 text-slate-800">1,500.00</td>
                            <td class="p-4">
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-md text-xs font-bold">45 In Stock</span>
                            </td>
                            <td class="p-4 text-center">
                                <button class="text-blue-600 hover:underline mr-3">Edit</button>
                                <button class="text-red-500 hover:underline">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="assets/js/script.js"></script>
</body>