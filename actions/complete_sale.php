<?php
/*
 * Completes a sale.
 *
 * Receives the cart as JSON: { "items": [ { "id": 1, "qty": 2 }, ... ] }
 *
 * Safety rules baked in:
 *  - Prices are read from the DATABASE, never from the browser (a user could
 *    tamper with the page and try to pay 1 rupee otherwise).
 *  - Stock is checked before selling; if any item is short, the whole sale
 *    is rejected.
 *  - Everything runs in a TRANSACTION: either the sale + all line items +
 *    all stock updates succeed together, or nothing changes at all.
 */

include '../includes/db.php';
include '../includes/auth.php';
include '../includes/settings.php';
require_login(); // must be logged in to take a sale

$tax_rate = (float) get_setting($conn, 'tax_rate', '0'); // percent

header('Content-Type: application/json');

// 1. Read + decode the JSON cart
$payload = json_decode(file_get_contents("php://input"), true);
$items   = $payload['items'] ?? [];

if (!is_array($items) || count($items) === 0) {
    echo json_encode(['success' => false, 'error' => 'Your cart is empty.']);
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;

// 2. Do it all as one transaction
$conn->begin_transaction();

try {
    $getProduct = $conn->prepare("SELECT name, price, cost_price, quantity FROM products WHERE id = ? FOR UPDATE");
    $subtotal = 0.0;
    $lines = []; // validated line data we'll insert after creating the sale

    foreach ($items as $item) {
        $pid = (int) ($item['id'] ?? 0);
        $qty = (int) ($item['qty'] ?? 0);

        if ($pid <= 0 || $qty <= 0) {
            throw new Exception('Invalid item in cart.');
        }

        $getProduct->bind_param("i", $pid);
        $getProduct->execute();
        $product = $getProduct->get_result()->fetch_assoc();

        if (!$product) {
            throw new Exception('A product in your cart no longer exists.');
        }
        if ($qty > (int) $product['quantity']) {
            throw new Exception('Not enough stock for "' . $product['name'] . '" (only ' . $product['quantity'] . ' left).');
        }

        $price_each = (float) $product['price'];
        $cost_each  = (float) $product['cost_price'];
        $subtotal  += $price_each * $qty;
        $lines[]    = ['id' => $pid, 'qty' => $qty, 'price' => $price_each, 'cost' => $cost_each];
    }

    // Apply the configured tax rate. total_amount = grand total (incl. tax).
    $tax   = round($subtotal * $tax_rate / 100, 2);
    $grand = $subtotal + $tax;

    // 3. Create the sale header row
    $insSale = $conn->prepare("INSERT INTO sales (user_id, total_amount, tax_amount) VALUES (?, ?, ?)");
    $insSale->bind_param("idd", $user_id, $grand, $tax);
    $insSale->execute();
    $sale_id = $conn->insert_id;

    // 4. Insert each line item + reduce that product's stock
    $insItem   = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price_each, cost_each) VALUES (?, ?, ?, ?, ?)");
    $dropStock = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");

    foreach ($lines as $ln) {
        $insItem->bind_param("iiidd", $sale_id, $ln['id'], $ln['qty'], $ln['price'], $ln['cost']);
        $insItem->execute();

        $dropStock->bind_param("ii", $ln['qty'], $ln['id']);
        $dropStock->execute();
    }

    // 5. Write an audit-trail log entry
    $logText = "Completed Sale #$sale_id — Rs " . number_format($grand, 2);
    $insLog  = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
    $insLog->bind_param("is", $user_id, $logText);
    $insLog->execute();

    // 6. All good — commit
    $conn->commit();

    echo json_encode([
        'success'  => true,
        'sale_id'  => $sale_id,
        'subtotal' => number_format($subtotal, 2),
        'tax'      => number_format($tax, 2),
        'total'    => number_format($grand, 2),
    ]);
} catch (Exception $e) {
    // Anything went wrong — undo everything
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
exit();
