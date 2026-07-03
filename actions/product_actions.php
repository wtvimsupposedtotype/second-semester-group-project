<?php
/*
 * Handles adding, editing, and deleting products.
 * Every branch uses prepared statements (safe from SQL injection) and
 * redirects back to inventory.php with a small status message.
 */

include '../includes/db.php';
include '../includes/auth.php';
include '../includes/log.php';
require_login(); // must be logged in to manage products

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../inventory.php");
    exit();
}

$op = $_POST['op'] ?? '';

// ---------- DELETE ----------
if ($op === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);

    // grab the name first, so the audit log is meaningful
    $look = $conn->prepare("SELECT name FROM products WHERE id = ?");
    $look->bind_param("i", $id);
    $look->execute();
    $prod = $look->get_result()->fetch_assoc();

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    record_log($conn, "Deleted product: " . ($prod['name'] ?? "#$id"));
    header("Location: ../inventory.php?msg=deleted");
    exit();
}

// ---------- ADD or UPDATE share the same form fields ----------
$name      = trim($_POST['name'] ?? '');
$sku       = trim($_POST['sku'] ?? '');
$category  = trim($_POST['category'] ?? '');
$price     = $_POST['price'] ?? '';
$cost      = $_POST['cost_price'] ?? 0;
$quantity  = $_POST['quantity'] ?? '';
$threshold = $_POST['low_stock_threshold'] ?? 5;

// Basic validation: name + valid numbers required.
if ($name === '' || !is_numeric($price) || !is_numeric($quantity)) {
    header("Location: ../inventory.php?msg=invalid");
    exit();
}

$price     = (float) $price;
$cost      = is_numeric($cost) ? (float) $cost : 0.0;
$quantity  = (int) $quantity;
$threshold = (int) $threshold;

/*
 * ---- Optional product picture ----
 * Returns a web path like "assets/uploads/products/xxx.jpg" on success,
 * or null if no (valid) file was uploaded. Files are stored relative to
 * the project root so the same path works from any page.
 */
function handle_product_image()
{
    if (empty($_FILES['image']['name']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // nothing uploaded
    }
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        return null; // upload glitch — silently skip, keeps existing pic on edit
    }

    // Limit to 2 MB.
    if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
        return null;
    }

    // Validate it's really an image, and pick a safe extension.
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];
    $info = getimagesize($_FILES['image']['tmp_name']);
    if ($info === false || !isset($allowed[$info['mime']])) {
        return null; // not a real image
    }
    $ext = $allowed[$info['mime']];

    $dir = __DIR__ . '/../assets/uploads/products';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    // Unique filename so uploads never overwrite each other.
    $filename = 'prod_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $target   = $dir . '/' . $filename;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        return null;
    }

    return 'assets/uploads/products/' . $filename;
}

$image_path = handle_product_image();

// Empty SKU / category should be stored as NULL (so blanks don't clash
// with the UNIQUE rule on sku, and empty categories stay clean).
$sku      = ($sku === '') ? null : $sku;
$category = ($category === '') ? null : $category;

if ($op === 'add') {
    $stmt = $conn->prepare(
        "INSERT INTO products (name, sku, category, image, price, cost_price, quantity, low_stock_threshold)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssssddii", $name, $sku, $category, $image_path, $price, $cost, $quantity, $threshold);

    if ($stmt->execute()) {
        record_log($conn, "Added product: $name");
        header("Location: ../inventory.php?msg=added");
    } else {
        // Most likely a duplicate SKU (the UNIQUE rule rejected it).
        header("Location: ../inventory.php?msg=dup&show=add");
    }
    exit();
}

if ($op === 'update') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($image_path !== null) {
        // A new picture was uploaded — replace the stored one.
        $stmt = $conn->prepare(
            "UPDATE products
             SET name = ?, sku = ?, category = ?, image = ?, price = ?, cost_price = ?, quantity = ?, low_stock_threshold = ?
             WHERE id = ?"
        );
        $stmt->bind_param("ssssddiii", $name, $sku, $category, $image_path, $price, $cost, $quantity, $threshold, $id);
    } else {
        // No new picture — leave the existing image column untouched.
        $stmt = $conn->prepare(
            "UPDATE products
             SET name = ?, sku = ?, category = ?, price = ?, cost_price = ?, quantity = ?, low_stock_threshold = ?
             WHERE id = ?"
        );
        $stmt->bind_param("sssddiii", $name, $sku, $category, $price, $cost, $quantity, $threshold, $id);
    }

    if ($stmt->execute()) {
        record_log($conn, "Updated product: $name");
        header("Location: ../inventory.php?msg=updated");
    } else {
        header("Location: ../inventory.php?msg=dup&edit_id=" . $id);
    }
    exit();
}

// Unknown operation
header("Location: ../inventory.php");
exit();
