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
</head>

<body>
    <main id="main-content" class="ml-64 p-8 bg-slate-100 min-h-screen transition-all duration-300">
        <button id="toggle-btn" class="flex flex-col gap-1 p-2 hover:bg-slate-200 rounded transition-colors">
            <span class="w-6 h-0.5 bg-slate-800"></span>
            <span class="w-6 h-0.5 bg-slate-800"></span>
            <span class="w-6 h-0.5 bg-slate-800"></span>
        </button>
        <div>
            <p class="text-2xl font-bold mt-4">Main Content Area</p>
        </div>
    </main>
    <script src="assets/js/script.js"></script>
</body>

</html>