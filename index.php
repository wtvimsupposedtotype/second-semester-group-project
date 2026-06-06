<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIBS - Login</title>

</head>

<style>
    body {
        margin: 0;
        font-family: Arial;
        background: #0d1117;
        color: white;
    }

    /* Main layout */
    .container {
        display: flex;
        height: 100vh;
    }

    /* Left side */
    .left {
        width: 40%;
        background: #111827;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .left h2 {
        color: #38bdf8;
    }

    /* Right side */
    .right {
        width: 60%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .right h2 {
        margin-bottom: 5px;
    }

    /* Inputs */
    input {
        width: 250px;
        padding: 10px;
        margin: 8px 0;
        border-radius: 5px;
        border: none;
    }


    /* Buttons */
    button {
        width: 260px;
        padding: 10px;
        margin-top: 10px;
        border: none;
        border-radius: 5px;
        background: #2563eb;
        color: white;

    }

    button:hover {
        background: #1e40af;
    }
</style>

</head>

<body>

    <div class="container">

        <!-- Left -->
        <div class="left">
            <h2>SMART INVENTORY</h2>
            <p>Secure Access Portal</p>
        </div>

        <!-- Right -->
        <div class="right">
            <h2>Welcome Back</h2>
            <p>Please enter your credentials</p>

            <?php if (isset($_GET['error'])): ?>
                <?php if ($_GET['error'] === 'pending_approval'): ?>
                    <p style="color:#fbbf24; max-width:260px; text-align:center;">⏳ Your account is waiting for admin approval. Please try again later.</p>
                <?php elseif ($_GET['error'] === 'access_denied'): ?>
                    <p style="color:#f87171; max-width:260px; text-align:center;">🚫 You don't have permission to view that page.</p>
                <?php else: ?>
                    <p style="color:#f87171; max-width:260px; text-align:center;">❌ Invalid username or password.</p>
                <?php endif; ?>
            <?php endif; ?>

            <form action="actions/login_process.php" method="POST" style="display: flex; flex-direction: column; align-items: center;">
                <input type="text" id="username" name="username" placeholder="Username">
                <input type="password" id="password" name="password" placeholder="Password">

                <button type="submit" name="clicked_role" value="admin">LOG IN AS ADMIN</button>

                <button type="submit" name="clicked_role" value="cashier">LOG IN AS CASHIER</button>
            </form>

            <p style="margin-top:16px; color:#94a3b8;">New cashier?
                <a href="register.php" style="color:#38bdf8;">Create an account</a>
            </p>
        </div>

    </div>


</body>

</html>