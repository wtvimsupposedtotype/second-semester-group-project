<?php include 'includes/db.php';
?>
<?php include 'includes/sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIBS - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
</head>

<body class="bg-slate-50">
    <main id="main-content" class="ml-64 min-h-screen transition-all duration-300">
        <!--Toggle button-->
        <header class="bg-white border-b border-slate-200 p-4 flex justify-between items-center sticky top-0 z-10">
            <div class="flex items-center gap-4">
                <button id="toggle-btn" class="p-2 hover:bg-slate-100 rounded-lg">
                    <span class="block w-6 h-0.5 bg-slate-600 mb-1"></span>
                    <span class="block w-6 h-0.5 bg-slate-600 mb-1"></span>
                    <span class="block w-6 h-0.5 bg-slate-600"></span>
                </button>
                <h1 class="text-xl font-bold text-slate-800">Dashboard</h1>
            </div>
        </header>
        <!-- Page content-->
        <div class="p-8 min-h-screen">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-5">
                <!-- Card 1 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                    <p class="text-sm font-medium text-slate-500">Total Products</p>
                    <p class="text-2xl font-bold text-slate-800">
                        <?php
                        // 1. SQL query to count rows in the products table
                        $sql = "SELECT COUNT(*) as total FROM products";
                        $result = $conn->query($sql);

                        // 2. Fetch the result count
                        $row = $result->fetch_assoc();

                        // 3. Print the number
                        echo $row['total'];
                        ?>
                    </p>
                </div>
                <!-- Card 2 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                    <p class="text-sm font-medium text-slate-500">Total Revenue</p>
                    <p class="text-2xl font-bold text-slate-800">
                        <?php
                        // 1. SQL query to SUM up the total_amount column
                        $sql = "SELECT SUM(total_amount) as total_revenue FROM sales";
                        $result = $conn->query($sql);
                        $row = $result->fetch_assoc();

                        // 2. If there are no sales yet, SUM returns NULL. Use ?? to default to 0.
                        $revenue = $row['total_revenue'] ?? 0;

                        // 3. Print it formatted nicely with two decimal places
                        echo number_format($revenue, 2) . " LKR";
                        ?>
                    </p>
                </div>
                <!-- Card 3 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                    <p class="text-sm font-medium text-slate-500">Total Sales</p>
                    <p class="text-2xl font-bold text-blue-600">
                        <?php
                        // Count how many individual invoice rows exist
                        $sql = "SELECT COUNT(*) as sales_count FROM sales";
                        $result = $conn->query($sql);
                        $row = $result->fetch_assoc();

                        echo $row['sales_count'];
                        ?>
                    </p>
                </div>
                <!-- Card 4 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                    <p class="text-sm font-medium text-slate-500">Orders Today</p>
                    <p class="text-2xl font-bold text-slate-800">
                        <?php
                        // COUNT rows where the date matches today's current date
                        $sql = "SELECT COUNT(*) as today_count FROM sales WHERE DATE(sale_date) = CURDATE()";
                        $result = $conn->query($sql);
                        $row = $result->fetch_assoc();

                        echo $row['today_count'];
                        ?>
                    </p>
                </div>
                <!-- Card 5 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                    <p class="text-sm font-medium text-slate-500">Low Stock Items</p>
                    <h3 class="text-2xl font-bold text-red-500">
                        <?php
                        // Count products where stock is between 1 and 10
                        $sql = "SELECT COUNT(*) as low_count FROM products WHERE quantity > 0 AND quantity <= 10";
                        $result = $conn->query($sql);
                        $row = $result->fetch_assoc();
                        echo $row['low_count'];
                        ?>
                    </h3>
                </div>
                <!-- Card 6 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                    <p class="text-sm font-medium text-slate-500">Out of Stock</p>
                    <h3 class="text-2xl font-bold text-slate-800">
                        <?php
                        // Count products where stock is exactly 0
                        $sql = "SELECT COUNT(*) as out_count FROM products WHERE quantity = 0";
                        $result = $conn->query($sql);
                        $row = $result->fetch_assoc();
                        echo $row['out_count'];
                        ?>
                    </h3>
                </div>
            </div>


            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mb-5">
                <!-- Sales Performance Section -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                        <h2 class="font-bold text-slate-800 mb-4">Sales Performance</h2>

                        <div style="height: 300px; width: 100%; min-height: 300px; display: block;">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                    <script>
                        // Makes sure the code runs ONLY after the page completely loads
                        document.addEventListener("DOMContentLoaded", function() {

                            fetch('actions/get-sales-data.php')
                                .then(response => response.json())
                                .then(data => {
                                    // FALLBACK: If database is empty, show today with 0 placeholder values
                                    if (data.length === 0) {
                                        data = [{
                                            sale_date: 'No Data Yet',
                                            daily_revenue: 0,
                                            daily_profit: 0
                                        }];
                                    }

                                    // 1. Map our database dates, revenues, and profits into separate arrays
                                    const labels = data.map(item => item.sale_date);
                                    const revenueValues = data.map(item => item.daily_revenue);
                                    const profitValues = data.map(item => item.daily_profit); // New line dataset array

                                    const ctx = document.getElementById('salesChart');
                                    if (ctx) {
                                        new Chart(ctx, {
                                            type: 'line',
                                            data: {
                                                labels: labels,
                                                datasets: [{
                                                        label: 'Gross Revenue (LKR)',
                                                        data: revenueValues,
                                                        borderColor: '#2563eb', // Corporate Blue
                                                        backgroundColor: 'rgba(37, 99, 235, 0.03)',
                                                        fill: true,
                                                        tension: 0.3,
                                                        borderWidth: 2.5
                                                    },
                                                    {
                                                        label: 'Net Profit (LKR)',
                                                        data: profitValues,
                                                        borderColor: '#10b981', // Emerald Green for Profit
                                                        backgroundColor: 'rgba(16, 185, 129, 0.05)',
                                                        fill: true,
                                                        tension: 0.3,
                                                        borderWidth: 2.5,
                                                        pointRadius: 3
                                                    }
                                                ]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false, // Allows it to obey our container height
                                                plugins: {
                                                    legend: {
                                                        display: true, // Shows the labels at the top so managers can toggle lines
                                                        position: 'top',
                                                        labels: {
                                                            boxWidth: 12,
                                                            font: {
                                                                weight: '500'
                                                            }
                                                        }
                                                    }
                                                },
                                                scales: {
                                                    y: {
                                                        beginAtZero: true,
                                                        grid: {
                                                            color: '#f1f5f9'
                                                        }
                                                    },
                                                    x: {
                                                        grid: {
                                                            color: '#f1f5f9'
                                                        } // Removes vertical line clutter for a modern look
                                                    }
                                                }
                                            }
                                        });
                                    }
                                })
                                .catch(error => console.error('Error loading chart data:', error));
                        });
                    </script>
                </div>

                <!-- Recent Transactions Table Section -->
                <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-800">Recent Transactions</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <?php
                        // 1. Fetch the 5 most recent sales records, newest first
                        $query = "SELECT id, total_amount, sale_date FROM sales ORDER BY sale_date DESC LIMIT 5";
                        $result = $conn->query($query);

                        // 2. Check if there are any transactions in the database
                        if ($result && $result->num_rows > 0) {
                        ?>
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="border-b border-slate-100 text-slate-400 font-medium">
                                        <th class="py-2">Invoice ID</th>
                                        <th class="py-2">Date</th>
                                        <th class="py-2 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 text-slate-700">
                                    <?php while ($row = $result->fetch_assoc()) { ?>
                                        <tr>
                                            <td class="py-3 font-medium text-blue-600">#<?php echo $row['id']; ?></td>
                                            <td class="py-3 text-slate-500">
                                                <?php echo date('M d, h:i A', strtotime($row['sale_date'])); ?>
                                            </td>
                                            <td class="py-3 text-right font-bold text-slate-800">
                                                <?php echo number_format($row['total_amount'], 2); ?> LKR
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php
                        } else {
                            // 3. Fallback message if the database table is completely empty
                        ?>
                            <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mb-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p class="text-sm italic">No recent transactions to display yet.</p>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
            <!-- Stock Alerts and Recommendations Table Section -->
            <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800">Stock Alerts and Recommendations</h3>
                </div>
                <div class="overflow-x-auto">
                    <?php
                    // 1. Fetch products running low (10 or less), ordered lowest first
                    $stock_query = "SELECT id, name, quantity, low_stock_threshold FROM products WHERE quantity <= 10 ORDER BY quantity ASC LIMIT 5";
                    $stock_result = $conn->query($stock_query);

                    // 2. If low stock products exist, build the table rows
                    if ($stock_result && $stock_result->num_rows > 0) {
                    ?>
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-400 font-medium">
                                    <th class="py-3 px-4">Item Name</th>
                                    <th class="py-3 px-4">Current Stock</th>
                                    <th class="py-3 px-4">Reorder Point</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-slate-700">
                                <?php
                                while ($product = $stock_result->fetch_assoc()) {
                                    $current_stock = $product['quantity'];

                                    // Set badge and action colors depending on absolute 0 vs low stock
                                    if ($current_stock == 0) {
                                        $badge_class = "bg-red-50 text-red-600 border-red-100";
                                        $status_text = "Out of Stock";
                                    } else {
                                        $badge_class = "bg-amber-50 text-amber-600 border-amber-100";
                                        $status_text = "Low Stock";
                                    }
                                ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="py-3 px-4 font-medium text-slate-800">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </td>

                                        <td class="py-3 px-4 font-bold <?php echo ($current_stock == 0) ? 'text-red-600' : 'text-amber-600'; ?>">
                                            <?php echo $current_stock; ?>
                                        </td>

                                        <td class="py-3 px-4 text-slate-500">
                                            <?php echo $product['low_stock_threshold'] ?? 10; ?>
                                        </td>

                                        <td class="py-3 px-4">
                                            <span class="text-xs px-2.5 py-0.5 rounded-full font-medium border <?php echo $badge_class; ?>">
                                                <?php echo $status_text; ?>
                                            </span>
                                        </td>

                                        <td class="py-3 px-4 text-center">
                                            <a href="manage-products.php?edit_id=<?php echo $product['id']; ?>"
                                                class="inline-flex items-center justify-center px-3 py-1 text-xs font-semibold rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all shadow-sm">
                                                Restock
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    <?php
                    } else {
                        // 3. Fallback view when all inventory items are completely healthy
                    ?>
                        <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mb-2 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm italic">No alerts to display. All inventory tracks safely above reorder points!</p>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>
    <script src="assets/js/script.js"></script>

</body>

</html>