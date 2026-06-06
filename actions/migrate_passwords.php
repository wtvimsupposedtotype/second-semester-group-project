<?php
/*
 * ONE-TIME USE SCRIPT.
 * This finds any PLAIN-TEXT passwords in your users table and replaces
 * them with secure hashes. It is safe to run more than once (it skips
 * passwords that are already hashed).
 *
 * HOW TO USE:
 *   1. Log in as an admin first.
 *   2. Open this in your browser:
 *      http://localhost/inventory-system/actions/migrate_passwords.php
 *   3. Read the result message.
 *
 * Safe to keep: only a logged-in admin can run it, and it skips any
 * password that is already hashed (so it never double-hashes).
 */

include '../includes/db.php';
include '../includes/auth.php';
require_login('admin'); // only an admin can run this maintenance tool

$result = $conn->query("SELECT id, password FROM users");

$hashed_count  = 0;
$skipped_count = 0;

while ($row = $result->fetch_assoc()) {
    $current = $row['password'];

    // password_get_info() tells us if the value is already a real hash.
    // algo === 0 (or null) means it's still plain text and needs hashing.
    $info = password_get_info($current);

    if ($info['algo'] === 0 || $info['algo'] === null) {
        $new_hash = password_hash($current, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_hash, $row['id']);
        $stmt->execute();
        $stmt->close();

        $hashed_count++;
    } else {
        $skipped_count++;
    }
}

echo "<h2>Password migration finished ✅</h2>";
echo "<p>Hashed (newly secured): <b>$hashed_count</b></p>";
echo "<p>Skipped (already hashed): <b>$skipped_count</b></p>";
echo "<p style='color:red'><b>Now DELETE this file (actions/migrate_passwords.php).</b></p>";
