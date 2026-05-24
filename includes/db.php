<?php
session_start();
$server = "localhost";
$user = "root";
$pass = "";
$dbname = "inventory_system";

$conn = new mysqli($server, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
