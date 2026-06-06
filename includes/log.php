<?php
/*
 * Audit-trail helper.
 *
 * Call this whenever something noteworthy happens, e.g.:
 *     include 'includes/log.php';
 *     record_log($conn, "Added product: Standard Mouse");
 *
 * If you don't pass a user id, it uses the logged-in user automatically.
 */

function record_log($conn, $action, $user_id = null)
{
    if ($user_id === null) {
        $user_id = $_SESSION['user_id'] ?? null;
    }

    $stmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $action);
    $stmt->execute();
    $stmt->close();
}
