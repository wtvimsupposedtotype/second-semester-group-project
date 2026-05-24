<?php
session_start();
include '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Grab the form inputs
    $user_input = $_POST['username'];
    $pass_input = $_POST['password'];
    $button_role = $_POST['clicked_role']; // This catches 'admin' or 'cashier'

    // 2. Security cleaning
    $user_input = $conn->real_escape_string($user_input);
    $pass_input = $conn->real_escape_string($pass_input);
    $button_role = $conn->real_escape_string($button_role);

    // 3. SQL query: The user MUST match the username, password, AND the role column
    $sql = "SELECT * FROM users WHERE username = '$user_input' AND password = '$pass_input' AND role = '$button_role'";
    $result = $conn->query($sql);

    // 4. Check the database results
    if ($result->num_rows == 1) {
        $user_data = $result->fetch_assoc();

        // Save details to session memory
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user_data['username'];
        $_SESSION['role'] = $user_data['role'];

        // 5. Redirect based on the successful match
        if ($button_role == 'admin') {
            header("Location: ../dashboard.php");
            exit();
        } else if ($button_role == 'cashier') {
            header("Location: ../billing.php");
            exit();
        }
    } else {
        // If username/password is wrong, OR if they clicked "Admin" but their account is a "Cashier"
        header("Location: ../index.php?error=invalid_credentials");
        exit();
    }
}
