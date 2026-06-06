<?php
/*
 * Saves the Settings form. Admin-only.
 * Only keys in the allow-list below can be written, so the form can't be
 * tricked into setting arbitrary values.
 */

include '../includes/db.php';
include '../includes/auth.php';
include '../includes/log.php';
require_login('admin');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../settings.php");
    exit();
}

// Which settings the form is allowed to update
$allowed = ['store_name', 'currency', 'address', 'tax_rate', 'low_stock_default'];

$stmt = $conn->prepare(
    "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
);

foreach ($allowed as $key) {
    if (isset($_POST[$key])) {
        $value = trim($_POST[$key]);
        $stmt->bind_param("ss", $key, $value);
        $stmt->execute();
    }
}

record_log($conn, "Updated system settings");

header("Location: ../settings.php?msg=saved");
exit();
