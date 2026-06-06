<?php
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/settings.php';
require_login('admin'); // settings (incl. Danger Zone) are admins only

$settings = get_settings($conn);
?>
<?php include 'includes/sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIBS - Settings</title>
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

            <?php if (isset($_GET['msg'])): ?>
                <?php if ($_GET['msg'] === 'saved'): ?>
                    <div class="mb-6 px-4 py-3 rounded-lg border text-sm bg-green-50 text-green-700 border-green-200">
                        ✅ Settings saved.
                    </div>
                <?php elseif ($_GET['msg'] === 'logs_cleared'): ?>
                    <div class="mb-6 px-4 py-3 rounded-lg border text-sm bg-slate-100 text-slate-700 border-slate-200">
                        🗑️ System logs cleared.
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Settings form (Business + Tax) -->
            <form action="actions/save_settings.php" method="POST" class="space-y-6">

                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                    <h2 class="text-lg font-bold text-slate-800 mb-1">Business Information</h2>
                    <p class="text-sm text-slate-400 mb-6">Details that identify your store (used on receipts &amp; headers).</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Store/Company Name</label>
                            <input type="text" name="store_name"
                                   value="<?php echo htmlspecialchars($settings['store_name'] ?? ''); ?>"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none text-slate-800">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Currency Symbol</label>
                            <input type="text" name="currency"
                                   value="<?php echo htmlspecialchars($settings['currency'] ?? ''); ?>"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none text-slate-800">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-600 mb-1">Business Address</label>
                            <input type="text" name="address"
                                   value="<?php echo htmlspecialchars($settings['address'] ?? ''); ?>"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none text-slate-800">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                    <h2 class="text-lg font-bold text-slate-800 mb-1">Tax &amp; Financial Settings</h2>
                    <p class="text-sm text-slate-400 mb-6">Global defaults for the shop.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Default Tax Rate (%)</label>
                            <input type="number" step="0.01" name="tax_rate"
                                   value="<?php echo htmlspecialchars($settings['tax_rate'] ?? '0'); ?>"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none text-slate-800">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Low Stock Threshold Alert</label>
                            <input type="number" name="low_stock_default"
                                   value="<?php echo htmlspecialchars($settings['low_stock_default'] ?? '10'); ?>"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none text-slate-800">
                            <p class="text-xs text-slate-400 mt-1">Default reorder point for new products.</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-bold shadow-md shadow-blue-200 transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>

            <!-- Danger Zone (separate from the settings form) -->
            <div class="bg-white rounded-xl shadow-sm border border-red-100 p-6 mt-6">
                <h2 class="text-lg font-bold text-red-600 mb-1">Danger Zone</h2>
                <p class="text-sm text-slate-400 mb-6">System maintenance operations.</p>

                <div class="flex flex-wrap gap-4">
                    <button type="button"
                            onclick="alert('To back up your database, open phpMyAdmin → select inventory_system → Export tab.');"
                            class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-lg font-medium transition-colors text-sm">
                        Export Database Backup (.sql)
                    </button>

                    <form action="actions/clear_logs.php" method="POST" class="inline"
                          onsubmit="return confirm('Permanently delete ALL system logs? This cannot be undone.');">
                        <button type="submit"
                                class="bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-lg font-medium transition-colors text-sm border border-red-200">
                            Clear System Logs
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </main>

    <script src="assets/js/script.js"></script>
</body>

</html>
