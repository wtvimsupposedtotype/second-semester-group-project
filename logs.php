<?php
// Include your database connection file
include 'includes/db.php';
include 'includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIBS - Logs & Audit Trail</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-50 font-sans antialiased">

    <main id="main-content" class="ml-64 min-h-screen transition-all duration-300">

        <!--Toggle button-->
        <header class="bg-white border-b border-slate-200 p-4 flex justify-between items-center sticky top-0 z-10">
            <div class="flex items-center gap-4">
                <button id="toggle-btn" class="p-2 hover:bg-slate-100 rounded-lg">
                    <span class="block w-6 h-0.5 bg-slate-600 mb-1"></span>
                    <span class="block w-6 h-0.5 bg-slate-600 mb-1"></span>
                    <span class="block w-6 h-0.5 bg-slate-600"></span>
                </button>
                <h1 class="text-xl font-bold text-slate-800">Logs</h1>
            </div>
        </header>

        <div class="p-8 min-h-screen">

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                <div class="xl:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-bold text-slate-800 text-lg">Activity Logs</h2>
                        <span class="px-2.5 py-1 text-xs font-semibold bg-blue-50 text-blue-600 rounded-md border border-blue-100">Live Feed</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-400 font-medium">
                                    <th class="py-3 px-4">Time</th>
                                    <th class="py-3 px-4">User Role</th>
                                    <th class="py-3 px-4">Event Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-slate-700">
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-3 px-4 text-slate-500 font-mono">10:00 AM</td>
                                    <td class="py-3 px-4"><span class="px-2 py-0.5 text-xs font-medium rounded bg-purple-50 text-purple-600 border border-purple-100">Admin</span></td>
                                    <td class="py-3 px-4 font-medium text-slate-800">Invoice Generated</td>
                                </tr>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-3 px-4 text-slate-500 font-mono">10:30 AM</td>
                                    <td class="py-3 px-4"><span class="px-2 py-0.5 text-xs font-medium rounded bg-emerald-50 text-emerald-600 border border-emerald-100">Cashier</span></td>
                                    <td class="py-3 px-4 font-medium text-slate-800">Stock Updated</td>
                                </tr>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-3 px-4 text-slate-500 font-mono">11:00 AM</td>
                                    <td class="py-3 px-4"><span class="px-2 py-0.5 text-xs font-medium rounded bg-purple-50 text-purple-600 border border-purple-100">Admin</span></td>
                                    <td class="py-3 px-4 font-medium text-slate-800">Price Changed</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                    <h2 class="font-bold text-slate-800 text-lg mb-4">Audit Trail</h2>

                    <div class="space-y-3">
                        <div class="flex items-start gap-3 p-3 rounded-lg bg-slate-50 border border-slate-100 text-sm text-slate-700">
                            <span class="text-emerald-500 mt-0.5">✔</span>
                            <p class="font-medium">Admin updated settings</p>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-lg bg-slate-50 border border-slate-100 text-sm text-slate-700">
                            <span class="text-emerald-500 mt-0.5">✔</span>
                            <p class="font-medium">Invoice <span class="text-blue-600 font-semibold">#1024</span> Generated</p>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-lg bg-slate-50 border border-slate-100 text-sm text-slate-700">
                            <span class="text-emerald-500 mt-0.5">✔</span>
                            <p class="font-medium">New cashier profile added</p>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-lg bg-red-50/50 border border-red-100 text-sm text-slate-700">
                            <span class="text-red-500 mt-0.5">⚠</span>
                            <p class="font-medium text-red-800">Low stock alert generated</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
    <script src="assets/js/script.js"></script>

</body>

</html>