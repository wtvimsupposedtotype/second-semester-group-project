<?php
include '../includes/db.php'; // connects to DB + starts session

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Grab + trim the inputs
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // 2. Basic validation: nothing empty
    if ($username === '' || $password === '') {
        header("Location: ../register.php?error=empty");
        exit();
    }

    // 3. Is this username already taken? (prepared statement = safe)
    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        header("Location: ../register.php?error=username_taken");
        exit();
    }
    $check->close();

    // 4. Hash the password — NEVER store it as plain text.
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // 5. Insert the new cashier as 'pending' (must be approved by an admin).
    $insert = $conn->prepare(
        "INSERT INTO users (username, password, role, status) VALUES (?, ?, 'cashier', 'pending')"
    );
    $insert->bind_param("ss", $username, $hash);

    if ($insert->execute()) {
        header("Location: ../register.php?success=1");
    } else {
        header("Location: ../register.php?error=unknown");
    }
    $insert->close();
    exit();
}
