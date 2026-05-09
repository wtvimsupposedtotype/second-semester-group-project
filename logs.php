<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIBS - logs</title>
    
</head>

<style>
.logs-container{
    color:white;
    padding:20px;
    font-family:Arial;
}
.table-box{
    background:#1f2937;
    padding:20px;
    border-radius:10px;
    margin-top:20px;
}
table{
    width:100%;
    border-collapse:collapse;
}
table th,
table td{
    padding:12px;
    border-bottom:1px solid gray;
    text-align:left;
}
.activity{
    background:#1f2937;
    margin-top:20px;
    padding:20px;
    border-radius:10px;
}
.activity p{
    border-bottom:1px solid gray;
    padding:10px 0;
}
</style>


<div class="logs-container">
    <h1>LOGS PAGE</h1>
    <div class="table-box">
        <h2>Activity Logs</h2>
        <table>
            <tr>
                <th>Time</th>
                <th>User</th>
                <th>Event</th>
            </tr>
            <tr>
                <td>10:00 AM</td>
                <td>Admin</td>
                <td>Invoice Generated</td>
            </tr>
            <tr>
                <td>10:30 AM</td>
                <td>Cashier</td>
                <td>Stock Updated</td>
            </tr>
            <tr>
                <td>11:00 AM</td>
                <td>Admin</td>
                <td>Price Changed</td>
            </tr>
        </table>
</div>

    <div class="activity">

        <h2>Audit Trail</h2>

        <p>✔ Admin updated settings</p>
        <p>✔ Invoice #1024 Generated</p>
        <p>✔ New cashier added</p>
        <p>✔ Low stock alert</p>

    </div>

</div>