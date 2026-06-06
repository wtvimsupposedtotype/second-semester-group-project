<?php
include '../includes/db.php';
header('Content-Type: application/json');

// Only logged-in users may read sales data. Anonymous requests get nothing.
if (empty($_SESSION['logged_in'])) {
    echo json_encode([]);
    exit();
}

$data = [];

try {
    // Revenue = selling price x qty.   Profit = (selling price - cost) x qty.
    // Both are summed per day from the individual sale line items.
    $sql = "SELECT DATE(s.sale_date) AS sale_date,
                   SUM(si.price_each * si.quantity) AS daily_revenue,
                   SUM((si.price_each - si.cost_each) * si.quantity) AS daily_profit
            FROM sales s
            JOIN sale_items si ON si.sale_id = s.id
            GROUP BY DATE(s.sale_date)
            ORDER BY sale_date DESC LIMIT 7";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
} catch (Exception $e) {
    // Safe catch-all fallback if table names differ during setup
}

if (empty($data)) {
    $data[] = [
        'sale_date' => date('Y-m-d'),
        'daily_revenue' => 0,
        'daily_profit' => 0
    ];
}

echo json_encode(array_reverse($data));
exit();
