<?php
include '../includes/db.php';
header('Content-Type: application/json');

$data = [];

try {
    // total_amount = Revenue 
    // (total_amount - total_cost) = Pure Profit
    $sql = "SELECT DATE(sale_date) as sale_date, 
                   SUM(total_amount) as daily_revenue,
                   SUM(total_amount - buying_cost) as daily_profit
            FROM sales 
            GROUP BY sale_date 
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
