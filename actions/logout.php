<?php
include '../includes/db.php'; // this also starts the session
include '../includes/log.php';

// Record the logout in the audit trail BEFORE we tear the session down,
// so record_log() can still see who the user was.
if (!empty($_SESSION['logged_in'])) {
    record_log($conn, "Logged out");
}

// Clear all session data, then destroy the session completely.
$_SESSION = [];
session_destroy();

// Send them back to the login page.
header("Location: ../index.php");
exit();
