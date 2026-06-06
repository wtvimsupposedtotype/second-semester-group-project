<?php
/*
 * Clears all audit-trail logs. Admin-only.
 * (Writes one fresh log entry afterwards so there's a record that it happened.)
 */

include '../includes/db.php';
include '../includes/auth.php';
include '../includes/log.php';
require_login('admin');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $conn->query("DELETE FROM logs");
    record_log($conn, "Cleared all system logs");
}

header("Location: ../settings.php?msg=logs_cleared");
exit();
