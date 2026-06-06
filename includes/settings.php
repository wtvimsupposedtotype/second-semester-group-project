<?php
/*
 * Settings helper.
 *
 * Load everything at once:
 *     include 'includes/settings.php';
 *     $settings = get_settings($conn);
 *     echo $settings['store_name'];
 *
 * Or grab a single value (with a fallback if it's missing):
 *     $tax = get_setting($conn, 'tax_rate', '0');
 */

function get_settings($conn)
{
    $settings = [];
    $res = $conn->query("SELECT setting_key, setting_value FROM settings");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $settings;
}

function get_setting($conn, $key, $default = '')
{
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ? $row['setting_value'] : $default;
}
