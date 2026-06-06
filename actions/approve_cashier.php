<?php
include '../includes/db.php'; // connects to DB + starts session
include '../includes/log.php';

// --- ADMIN GUARD: only logged-in admins may run this ---
if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../index.php?error=access_denied");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = (int) $_POST['user_id'];   // cast to int = safe
    $action  = $_POST['action'];          // 'approve' or 'reject'

    // Look up the cashier's username first, so the log reads nicely.
    $look = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $look->bind_param("i", $user_id);
    $look->execute();
    $target = $look->get_result()->fetch_assoc();
    $cashier_name = $target['username'] ?? "#$user_id";

    if ($action === 'approve') {
        // Flip the cashier's status to approved.
        $stmt = $conn->prepare("UPDATE users SET status = 'approved' WHERE id = ? AND role = 'cashier'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        record_log($conn, "Approved cashier: $cashier_name");
    } elseif ($action === 'reject') {
        // Reject = delete the pending request entirely.
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'cashier' AND status = 'pending'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        record_log($conn, "Rejected cashier: $cashier_name");
    }
}

// Back to the management page either way.
header("Location: ../manage-cashiers.php");
exit();
