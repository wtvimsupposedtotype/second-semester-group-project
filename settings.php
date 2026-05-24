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
                <h1 class="text-xl font-bold text-slate-800">System Settings</h1>
            </div>
        </header>

        <div class="p-8 max-w-4xl mx-auto">
            <div class="space-y-6">

                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                    <h2 class="text-lg font-bold text-slate-800 mb-1">Business Information</h2>
                    <p class="text-sm text-slate-400 mb-6">Configure the details that appear on printed customer receipts.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Store/Company Name</label>
                            <input type="text" value="SIBS Ltd" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none text-slate-800">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Currency Symbol</label>
                            <input type="text" value="LKR" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none text-slate-800">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-600 mb-1">Business Address</label>
                            <input type="text" value="Colombo, Sri Lanka" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none text-slate-800">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                    <h2 class="text-lg font-bold text-slate-800 mb-1">Tax & Financial Settings</h2>
                    <p class="text-sm text-slate-400 mb-6">Manage global calculations for checkout transactions.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Default Tax Rate (%)</label>
                            <input type="number" value="0" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none text-slate-800">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Low Stock Threshold Alert</label>
                            <input type="number" value="10" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none text-slate-800">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-red-100 p-6">
                    <h2 class="text-lg font-bold text-red-600 mb-1">Danger Zone</h2>
                    <p class="text-sm text-slate-400 mb-6">System maintenance and database backup operations.</p>

                    <div class="flex flex-wrap gap-4">
                        <button class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-lg font-medium transition-colors text-sm">
                            Export Database Backup (.sql)
                        </button>
                        <button class="bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-lg font-medium transition-colors text-sm border border-red-200">
                            Clear System Logs
                        </button>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-bold shadow-md shadow-blue-200 transition-colors">
                        Save Changes
                    </button>
                </div>

            </div>
        </div>
    </main>

    <script src="assets/js/script.js"></script>
</body>