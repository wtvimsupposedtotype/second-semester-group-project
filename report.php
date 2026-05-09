<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIBS - Report</title>
    
</head>
<style>

.reports-container{
    color:white;
    padding:20px;
    font-family:Arial;
}
.cards{
    display:flex;
    gap:20px;
    margin-top:20px;
}
.card{
    background:#1f2937;
    padding:20px;
    border-radius:10px;
    width:200px;
}
.card h3{
    color:#38bdf8;
    margin-bottom:10px;
}
.chart{
    background:#1f2937;
    margin-top:20px;
    padding:20px;
    border-radius:10px;
}

.graph{
    height:220px;
    border-left:2px solid white;
    border-bottom:2px solid white;
    position:relative;
    margin-top:20px;
}

.line{
    width:80%;
    height:3px;
    background:#38bdf8;
    position:absolute;
    top:100px;
    left:20px;
    transform:rotate(-10deg);
}
</style>


<div class="reports-container">
    <h1>REPORTS PAGE</h1>
    <div class="cards">
        <div class="card">
            <h3>Total Revenue</h3>
            <h2>$25,000</h2>
        </div>
        <div class="card">
            <h3>Total Orders</h3>
            <h2>120</h2>
        </div>
        <div class="card">
            <h3>Low Stock</h3>
            <h2>5 Items</h2>
        </div>
    </div>

    <div class="chart">

        <h2>Sales Performance</h2>

        <div class="graph">
            <div class="line"></div>
        </div>

    </div>

</div>