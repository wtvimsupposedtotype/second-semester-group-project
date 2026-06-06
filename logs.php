<?php
// Include your database connection file
include 'includes/db.php';
include 'includes/auth.php';
require_login(); // must be logged in to view logs

// Pull the newest 50 log entries, joined to the user who triggered them
$logs = [];
$res = $conn->query("SELECT l.action, l.timestamp, u.username, u.role
                     FROM logs l
                     LEFT JOIN users u ON l.user_id = u.id
                     ORDER BY l.timestamp DESC
                     LIMIT 50");
while ($row = $res->fetch_assoc()) {
    $logs[] = $row;
}

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
                                <?php if (count($logs) === 0): ?>
                                    <tr>
                                        <td colspan="3" class="py-8 px-4 text-center text-slate-400 italic">
                                            No activity logged yet. Actions like logins and sales will appear here.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log): ?>
                                        <?php
                                            $role = $log['role'] ?? null;
                                            if ($role === 'admin') {
                                                $role_badge = 'bg-purple-50 text-purple-600 border-purple-100';
                                                $role_text  = 'Admin';
                                            } elseif ($role === 'cashier') {
                                                $role_badge = 'bg-emerald-50 text-emerald-600 border-emerald-100';
                                                $role_text  = 'Cashier';
                                            } else {
                                                $role_badge = 'bg-slate-100 text-slate-500 border-slate-200';
                                                $role_text  = 'System';
                                            }
                                        ?>
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="py-3 px-4 text-slate-500 font-mono">
                                                <?php echo date('M d, h:i A', strtotime($log['timestamp'])); ?>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="px-2 py-0.5 text-xs font-medium rounded border <?php echo $role_badge; ?>">
                                                    <?php echo $role_text; ?><?php echo $log['username'] ? ' · ' . htmlspecialchars($log['username']) : ''; ?>
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 font-medium text-slate-800">
                                                <?php echo htmlspecialchars($log['action']); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                    <h2 class="font-bold text-slate-800 text-lg mb-4">Recent Activity</h2>

                    <div class="space-y-3">
                        <?php if (count($logs) === 0): ?>
                            <p class="text-slate-400 text-sm italic">Nothing here yet.</p>
                        <?php else: ?>
                            <?php foreach (array_slice($logs, 0, 6) as $log): ?>
                                <div class="flex items-start gap-3 p-3 rounded-lg bg-slate-50 border border-slate-100 text-sm text-slate-700">
                                    <span class="text-emerald-500 mt-0.5">✔</span>
                                    <div>
                                        <p class="font-medium"><?php echo htmlspecialchars($log['action']); ?></p>
                                        <p class="text-xs text-slate-400 mt-0.5">
                                            <?php echo htmlspecialchars($log['username'] ?? 'System'); ?> ·
                                            <?php echo date('M d, h:i A', strtotime($log['timestamp'])); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </main>
    <script src="assets/js/script.js"></script>

</body>

</html>