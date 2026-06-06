<?php
include 'includes/db.php';
include 'includes/auth.php';
require_login(); // must be logged in to view reports

// --- Summary numbers ---
$total_revenue = $conn->query("SELECT SUM(total_amount - tax_amount) AS t FROM sales")->fetch_assoc()['t'] ?? 0; // net of tax
$total_orders  = $conn->query("SELECT COUNT(*) AS c FROM sales")->fetch_assoc()['c'] ?? 0;
$low_stock     = $conn->query("SELECT COUNT(*) AS c FROM products WHERE quantity <= low_stock_threshold")->fetch_assoc()['c'] ?? 0;

// --- Monthly revenue for the current year (Jan..Dec), zero-filled ---
$year    = date('Y');
$monthly = array_fill(1, 12, 0); // months 1..12 default to 0
$mq = $conn->query("SELECT MONTH(sale_date) AS m, SUM(total_amount - tax_amount) AS rev
                    FROM sales
                    WHERE YEAR(sale_date) = $year
                    GROUP BY MONTH(sale_date)");
while ($r = $mq->fetch_assoc()) {
    $monthly[(int) $r['m']] = (float) $r['rev'];
}
$month_labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$month_values = array_values($monthly);
?>
<?php include 'includes/sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIBS - Financial Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
</head>

<body class="bg-slate-50 font-sans antialiased">

    <main id="main-content" class="ml-64 min-h-screen transition-all duration-300">

        <header class="bg-white border-b border-slate-200 p-4 flex justify-between items-center sticky top-0 z-10">
            <div class="flex items-center gap-4">
                <button id="toggle-btn" class="p-2 hover:bg-slate-100 rounded-lg">
                    <span class="block w-6 h-0.5 bg-slate-600 mb-1"></span>
                    <span class="block w-6 h-0.5 bg-slate-600 mb-1"></span>
                    <span class="block w-6 h-0.5 bg-slate-600"></span>
                </button>
                <h1 class="text-xl font-bold text-slate-800">Reports</h1>
            </div>

        </header>
        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 w-full">

                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                    <h3 class="text-sm font-medium text-slate-400 uppercase tracking-wider">Total Revenue</h3>
                    <p class="text-2xl font-bold text-slate-800 mt-2"><?php echo number_format($total_revenue, 2); ?> <span class="text-xs text-slate-400 font-normal">LKR</span></p>
                    <span class="text-xs text-emerald-500 font-medium mt-1 inline-block">✔ Live synced database</span>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                    <h3 class="text-sm font-medium text-slate-400 uppercase tracking-wider">Total Orders</h3>
                    <p class="text-2xl font-bold text-slate-800 mt-2"><?php echo $total_orders; ?></p>
                    <span class="text-xs text-blue-500 font-medium mt-1 inline-block">Processed invoices</span>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                    <h3 class="text-sm font-medium text-slate-400 uppercase tracking-wider">Low Stock Items</h3>
                    <p class="text-2xl font-bold text-red-600 mt-2"><?php echo $low_stock; ?> <span class="text-xs text-slate-400 font-normal">Items</span></p>
                    <span class="text-xs text-amber-500 font-medium mt-1 inline-block">Requires reordering</span>
                </div>

            </div>

            <div class="w-full bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="font-bold text-slate-800 text-lg">Sales Analysis</h2>
                        <p class="text-xs text-slate-400">Monthly overview of gross earnings</p>
                    </div>
                    <span class="text-xs px-2.5 py-1 font-semibold bg-blue-50 text-blue-600 rounded-md border border-blue-100">Yearly Timeline</span>
                </div>

                <div style="height: 300px; width: 100%; position: relative;">
                    <canvas id="reportsMonthlyChart"></canvas>
                </div>
            </div>



            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const canvas = document.getElementById('reportsMonthlyChart');
                    if (!canvas || typeof Chart === 'undefined') return;

                    // Real monthly revenue for the current year, pulled from the database
                    const months = <?php echo json_encode($month_labels); ?>;
                    const values = <?php echo json_encode($month_values); ?>;

                    new Chart(canvas, {
                        type: 'line',
                        data: {
                            labels: months,
                            datasets: [{
                                label: 'Monthly Earnings (LKR)',
                                data: values,
                                borderColor: '#0284c7', // Sky blue tone for analytical report style
                                backgroundColor: 'rgba(2, 132, 199, 0.04)',
                                fill: true,
                                tension: 0.4, // Curved analytics lines
                                borderWidth: 3,
                                pointBackgroundColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                } // Hidden for a minimalist dashboard look
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    suggestedMax: 50000, // Provides realistic vertical height room out-of-the-box
                                    ticks: {
                                        callback: value => value.toLocaleString() + ' LKR'
                                    },
                                    grid: {
                                        color: '#f8fafc'
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
        </div>
    </main>
</body>

</html>