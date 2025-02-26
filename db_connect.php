<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "plant_monitor_db";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
