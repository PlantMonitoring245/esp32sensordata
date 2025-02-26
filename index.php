<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include("db_connect.php");

// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "plant_monitor_db";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed!"]));
}

// Fetch latest sensor data
$sql = "SELECT * FROM sensor_data ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);
$data = $result->fetch_assoc() ?: ["moisture" => "0", "temperature" => "0", "humidity" => "0", "motion" => "0", "light_intensity" => "0"];
$conn->close();

// Handle AJAX Request
if (isset($_GET['ajax'])) {
    echo json_encode($data);
    exit;
}

// Handle Pump Control
if (isset($_GET['pump'])) {
    $pumpState = $_GET['pump']; // 1 for ON, 0 for OFF
    file_put_contents("pump_status.txt", $pumpState);
    echo json_encode(["pump" => $pumpState]);
    exit;
}

// Get Pump Status
$pumpStatus = file_exists("pump_status.txt") ? file_get_contents("pump_status.txt") : "0";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plant Monitoring Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #eef7f9; }
        .navbar { background: linear-gradient(to right, #2E7D32, #66BB6A); padding: 15px; }
        .navbar-brand, .navbar-nav .nav-link { color: white; font-weight: bold; }
        .container { margin-top: 40px; }
        .card {
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            background: white;
            transition: all 0.3s ease-in-out;
        }
        .card:hover { transform: translateY(-5px); }
        h2 { font-size: 22px; font-weight: bold; }
        p { font-size: 24px; font-weight: bold; }
        .motion-active { color: green; font-weight: bold; animation: blink 1s infinite alternate; }
        .motion-inactive { color: red; font-weight: bold; }
        @keyframes blink { 0% { opacity: 1; } 100% { opacity: 0.5; } }
        .alert { display: none; margin-top: 20px; }
        .sensor-image { width: 80px; height: 80px; margin-top: 10px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"><i class="fas fa-seedling"></i> Plant Monitoring Dashboard</a>
        <div class="d-flex">
            <a href="graph.php" class="btn btn-primary me-2">View Graph</a>
            <a href="fetch_data.php" class="btn btn-warning me-2">View Table</a> 
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="alert alert-danger" id="moistureAlert">
        ⚠ Soil moisture is low! Pump may be required.
    </div>
  
    <div class="row justify-content-center g-4">
        <div class="col-md-3">
            <div class="card">
                <h2>Soil Moisture</h2>
                <canvas id="moistureGauge"></canvas>
                <p id="moistureText"><?php echo $data["moisture"]; ?>%</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <h2>Temperature</h2>
                <canvas id="temperatureGauge"></canvas>
                <p id="temperatureText"><?php echo $data["temperature"]; ?>°C</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <h2>Humidity</h2>
                <canvas id="humidityGauge"></canvas>
                <p id="humidityText"><?php echo $data["humidity"]; ?>%</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <h2>Light Intensity</h2>
                <canvas id="lightGauge"></canvas>
                <p id="lightText"><?php echo $data["light_intensity"]; ?>%</p>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <button class="btn btn-success" id="pumpOn">Turn Pump ON</button>
        <button class="btn btn-danger" id="pumpOff">Turn Pump OFF</button>
    </div>
</div>

<script>
    let charts = {};

    function fetchData() {
        fetch("index.php?ajax=1")
            .then(response => response.json())
            .then(data => {
                document.getElementById("moistureText").innerText = data.moisture + "%";
                document.getElementById("temperatureText").innerText = data.temperature + "°C";
                document.getElementById("humidityText").innerText = data.humidity + "%";
                document.getElementById("lightText").innerText = data.light_intensity + "%";

                if (data.moisture < 35) {
                    document.getElementById("moistureAlert").style.display = "block";
                } else {
                    document.getElementById("moistureAlert").style.display = "none";
                }

                updateGauge("moistureGauge", data.moisture, "#4CAF50");
                updateGauge("temperatureGauge", data.temperature, "#FF5733");
                updateGauge("humidityGauge", data.humidity, "#3498db");
                updateGauge("lightGauge", data.light_intensity, "#f1c40f");
            })
            .catch(error => console.error("Error fetching data:", error));
    }

    function updateGauge(canvasId, value, color) {
        let ctx = document.getElementById(canvasId).getContext("2d");
        if (charts[canvasId]) charts[canvasId].destroy();
        charts[canvasId] = new Chart(ctx, {
            type: "doughnut",
            data: {
                labels: ["Value", "Remaining"],
                datasets: [{
                    data: [value, 100 - value],
                    backgroundColor: [color, "#ddd"]
                }]
            },
            options: { cutout: "70%" }
        });
    }

    document.getElementById("pumpOn").addEventListener("click", () => fetch("index.php?pump=1").then(() => alert("Pump Turned ON")));
    document.getElementById("pumpOff").addEventListener("click", () => fetch("index.php?pump=0").then(() => alert("Pump Turned OFF")));

    setInterval(fetchData, 5000);
    fetchData();
</script>

</body>
</html>
