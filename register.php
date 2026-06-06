<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIBS - Sign Up</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="m-0 font-sans bg-[#0d1117] text-white">

    <div class="flex h-screen">

        <!-- Left -->
        <div class="w-2/5 bg-[#111827] flex flex-col justify-center items-center">
            <h2 class="text-2xl font-bold text-sky-400">SMART INVENTORY</h2>
            <p class="text-slate-300">Cashier Registration</p>
        </div>

        <!-- Right -->
        <div class="w-3/5 flex flex-col justify-center items-center px-6">
            <h2 class="text-xl font-bold mb-1">Create a Cashier Account</h2>
            <p class="text-slate-400 mb-4 text-center">Your account needs admin approval before you can log in.</p>

            <?php if (isset($_GET['error'])): ?>
                <?php if ($_GET['error'] === 'username_taken'): ?>
                    <p class="text-red-400 max-w-xs text-center mb-2">That username is already taken. Try another.</p>
                <?php elseif ($_GET['error'] === 'empty'): ?>
                    <p class="text-red-400 max-w-xs text-center mb-2">Please fill in both username and password.</p>
                <?php else: ?>
                    <p class="text-red-400 max-w-xs text-center mb-2">Something went wrong. Please try again.</p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <p class="text-green-400 max-w-xs text-center mb-2">✅ Account created! An admin will approve it soon. You can then log in.</p>
            <?php endif; ?>

            <form action="actions/register_process.php" method="POST" class="flex flex-col items-center w-full max-w-xs">
                <input type="text" name="username" placeholder="Choose a username" required
                       class="w-full px-3 py-2.5 my-2 rounded-md border-none text-slate-800 outline-none focus:ring-2 focus:ring-sky-500">
                <input type="password" name="password" placeholder="Choose a password" required
                       class="w-full px-3 py-2.5 my-2 rounded-md border-none text-slate-800 outline-none focus:ring-2 focus:ring-sky-500">
                <button type="submit"
                        class="w-full px-3 py-2.5 mt-3 rounded-md bg-blue-600 hover:bg-blue-800 text-white font-semibold transition-colors">
                    SIGN UP AS CASHIER
                </button>
            </form>

            <p class="text-slate-400 text-sm mt-4">
                Already have an account?
                <a href="index.php" class="text-sky-400 hover:underline">Log in here</a>
            </p>
        </div>

    </div>

</body>

</html>
