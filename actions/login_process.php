<?php
include '../includes/db.php'; // this also starts the session
include '../includes/log.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Grab the form inputs
    $user_input  = $_POST['username'];
    $pass_input  = $_POST['password'];
    $button_role = $_POST['clicked_role']; // 'admin' or 'cashier'

    // 2. Look the user up by USERNAME only, using a prepared statement.
    //    The "?" placeholder means the input can never break out and run
    //    its own SQL (this kills SQL-injection attacks).
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $user_input);
    $stmt->execute();
    $result = $stmt->get_result();

    // 3. If we found that username, check the password + role IN PHP.
    if ($result->num_rows === 1) {
        $user_data = $result->fetch_assoc();

        // password_verify() re-hashes what they typed and compares it to
        // the stored hash. We never "unscramble" the stored password.
        $password_ok = password_verify($pass_input, $user_data['password']);
        $role_ok     = ($user_data['role'] === $button_role);

        // A cashier must be approved by an admin first. Admins are never blocked.
        if ($password_ok && $role_ok
            && $user_data['role'] === 'cashier'
            && $user_data['status'] !== 'approved') {
            header("Location: ../index.php?error=pending_approval");
            exit();
        }

        if ($password_ok && $role_ok) {
            // Fresh session id on login = good security habit.
            session_regenerate_id(true);

            $_SESSION['logged_in'] = true;
            $_SESSION['user_id']   = $user_data['id'];
            $_SESSION['username']  = $user_data['username'];
            $_SESSION['role']      = $user_data['role'];

            record_log($conn, "Logged in", $user_data['id']);

            if ($button_role === 'admin') {
                header("Location: ../dashboard.php");
            } else {
                header("Location: ../billing.php");
            }
            exit();
        }
    }

    // Wrong username, wrong password, OR wrong role button = same generic error.
    // (Telling attackers *which* part was wrong helps them, so we don't.)
    header("Location: ../index.php?error=invalid_credentials");
    exit();
}
