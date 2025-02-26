<?php
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

// Fetch the latest 10 records for the graph
$sql = "SELECT * FROM sensor_data ORDER BY id DESC LIMIT 10";
$result = $conn->query($sql);

$timestamps = [];
$moistureData = [];
$temperatureData = [];
$humidityData = [];

while ($row = $result->fetch_assoc()) {
    $timestamps[] = $row["timestamp"];
    $moistureData[] = $row["moisture"];
    $temperatureData[] = $row["temperature"];
    $humidityData[] = $row["humidity"];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plant Monitoring Graph</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #eef7f9; }
        .container { margin-top: 40px; text-align: center; }
        .chart-container {
            width: 100vw; /* Full width */
            height: 75vh; /* Increased height */
            max-width: 1400px;
            margin: auto;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }
        canvas { width: 100% !important; height: 100% !important; }
    </style>
</head>
<body>

<div class="container">
    <h2 class="mb-4">Plant Monitoring Data (Last 10 Entries)</h2>
    <div class="chart-container">
        <canvas id="sensorChart"></canvas>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('sensorChart').getContext('2d');
        const sensorChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($timestamps); ?>,
                datasets: [
                    {
                        label: 'Soil Moisture (%)',
                        data: <?php echo json_encode($moistureData); ?>,
                        borderColor: '#4CAF50',
                        backgroundColor: 'rgba(76, 175, 80, 0.3)',
                        borderWidth: 4,
                        fill: true,
                        pointRadius: 6,
                        tension: 0.3
                    },
                    {
                        label: 'Temperature (°C)',
                        data: <?php echo json_encode($temperatureData); ?>,
                        borderColor: '#FF5733',
                        backgroundColor: 'rgba(255, 87, 51, 0.3)',
                        borderWidth: 4,
                        fill: true,
                        pointRadius: 6,
                        tension: 0.3
                    },
                    {
                        label: 'Humidity (%)',
                        data: <?php echo json_encode($humidityData); ?>,
                        borderColor: '#2196F3',
                        backgroundColor: 'rgba(33, 150, 243, 0.3)',
                        borderWidth: 4,
                        fill: true,
                        pointRadius: 6,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        ticks: { color: '#000', font: { size: 14 } }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#000', font: { size: 14 } }
                    }
                },
                plugins: {
                    legend: { display: true, position: 'top', labels: { font: { size: 16 } } },
                    tooltip: { enabled: true, mode: 'index', intersect: false }
                }
            }
        });
    });
</script>

</body>
</html>
