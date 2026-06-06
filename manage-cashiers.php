<?php
include 'includes/db.php'; // connects to DB + starts session
include 'includes/auth.php';
require_login('admin'); // admins only

// Pending cashiers (need approval)
$pending = $conn->query("SELECT id, username, created_at FROM users
                         WHERE role = 'cashier' AND status = 'pending'
                         ORDER BY created_at DESC");

// Already-approved cashiers
$approved = $conn->query("SELECT id, username, created_at FROM users
                          WHERE role = 'cashier' AND status = 'approved'
                          ORDER BY username ASC");
?>
<?php include 'includes/sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIBS - Manage Cashiers</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-50">

    <main id="main-content" class="ml-64 min-h-screen transition-all duration-300">
        <header class="bg-white border-b border-slate-200 p-4 flex items-center gap-4 sticky top-0 z-10">
            <h1 class="text-xl font-bold text-slate-800">Manage Cashiers</h1>
        </header>

        <div class="p-8 max-w-4xl mx-auto space-y-8">

            <!-- PENDING -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h2 class="text-lg font-bold text-slate-800 mb-1">Pending Approvals</h2>
                <p class="text-sm text-slate-400 mb-6">New cashiers waiting for you to approve their sign-up.</p>

                <?php if ($pending->num_rows === 0): ?>
                    <p class="text-slate-400 text-sm italic">No pending requests right now. 🎉</p>
                <?php else: ?>
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-sm text-slate-500 border-b border-slate-100">
                                <th class="py-2">Username</th>
                                <th class="py-2">Requested</th>
                                <th class="py-2 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $pending->fetch_assoc()): ?>
                            <tr class="border-b border-slate-50">
                                <td class="py-3 font-medium text-slate-800"><?php echo htmlspecialchars($row['username']); ?></td>
                                <td class="py-3 text-sm text-slate-500"><?php echo htmlspecialchars($row['created_at']); ?></td>
                                <td class="py-3 text-right">
                                    <form action="actions/approve_cashier.php" method="POST" class="inline">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$row['id']; ?>">
                                        <button type="submit" name="action" value="approve"
                                                class="bg-green-600 hover:bg-green-700 text-white text-sm px-3 py-1.5 rounded-lg font-medium transition-colors">
                                            Approve
                                        </button>
                                    </form>
                                    <form action="actions/approve_cashier.php" method="POST" class="inline">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$row['id']; ?>">
                                        <button type="submit" name="action" value="reject"
                                                class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-sm px-3 py-1.5 rounded-lg font-medium transition-colors">
                                            Reject
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- APPROVED -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h2 class="text-lg font-bold text-slate-800 mb-1">Active Cashiers</h2>
                <p class="text-sm text-slate-400 mb-6">Cashiers who can currently log in.</p>

                <?php if ($approved->num_rows === 0): ?>
                    <p class="text-slate-400 text-sm italic">No approved cashiers yet.</p>
                <?php else: ?>
                    <ul class="divide-y divide-slate-50">
                        <?php while ($row = $approved->fetch_assoc()): ?>
                            <li class="py-3 flex items-center justify-between">
                                <span class="font-medium text-slate-800"><?php echo htmlspecialchars($row['username']); ?></span>
                                <span class="text-xs bg-green-50 text-green-700 px-2 py-1 rounded-full">approved</span>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <script src="assets/js/script.js"></script>
</body>

</html>
