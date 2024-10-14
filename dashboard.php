<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: loginPlusRegistration.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$sql_user = "SELECT * FROM users WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();

// Fetch reports for the logged-in user
$sql_reports = "SELECT issue_type, description, created_at FROM issues WHERE user_id = ? ORDER BY created_at DESC";
$stmt_reports = $conn->prepare($sql_reports);
$stmt_reports->bind_param("i", $user_id);
$stmt_reports->execute();
$result_reports = $stmt_reports->get_result();

$reports = [];
while ($row = $result_reports->fetch_assoc()) {
    $reports[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .chart-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .chart-box {
            flex: 1;
            max-width: 500px;
            height: 400px;
        }

        canvas {
            width: 100% !important;
            height: 100% !important;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <!-- Vertical Navigation Bar -->
            <div class="col-md-3">
                <div class="d-flex flex-column">
                    <a href="dashboard.php" class="btn btn-light mb-3 w-100">Dashboard</a>
                    <a href="schedule_pickup.php" class="btn btn-success mb-3 w-100">Schedule Pickup</a>
                    <a href="pickup_history.php" class="btn btn-primary mb-3 w-100">Pickup History</a>
                    <a href="report_issue.php" class="btn btn-warning mb-3 w-100">Report Issues</a>
                    <a href="logout.php" class="btn btn-danger w-100">Logout</a>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-md-9">
                <h1 class="text-center">Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
                <p class="text-center mt-3">Use the navigation on the left to manage your waste pickup activities.</p>

                <!-- Chart Row -->
                <div class="chart-container mt-5">
                    <!-- Pie Chart -->
                    <div class="chart-box">
                        <h2 class="text-center">Waste Types Distribution</h2>
                        <canvas id="wastePieChart"></canvas>
                    </div>

                    <!-- Line Chart -->
                    <div class="chart-box">
                        <h2 class="text-center">Total Waste Collected per Day</h2>
                        <canvas id="wasteLineChart"></canvas>
                    </div>
                </div>

                <!-- Reported Issues Section -->
                <div class="mt-5">
                    <h2 class="text-center">Your Reported Issues</h2>

                    <?php if (count($reports) > 0): ?>
                        <!-- Display Reports in a Table -->
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Report Type</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $report): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($report['issue_type']); ?></td>
                                        <td><?php echo htmlspecialchars($report['description']); ?></td>
                                        <td><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($report['created_at']))); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-center">You have not reported any issues yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fetch data from API
        fetch('api/waste_data.php')
            .then(response => response.json())
            .then(data => {
                // Prepare Pie Chart Data
                const wasteTypes = data.waste_types.map(item => item.type);
                const wasteAmounts = data.waste_types.map(item => item.total);

                // Update Pie Chart Data
                const pieData = {
                    labels: wasteTypes,
                    datasets: [{
                        data: wasteAmounts,
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'],
                        hoverBackgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                    }]
                };

                // Prepare Line Chart Data
                const days = data.daily_waste.map(item => item.day);
                const wastePerDay = data.daily_waste.map(item => item.total);

                // Update Line Chart Data
                const lineData = {
                    labels: days,
                    datasets: [{
                        label: 'Total Waste Collected (kg)',
                        data: wastePerDay,
                        borderColor: '#36A2EB',
                        fill: false,
                        tension: 0.1
                    }]
                };

                // Render Charts
                const pieCtx = document.getElementById('wastePieChart').getContext('2d');
                new Chart(pieCtx, {
                    type: 'pie',
                    data: pieData
                });

                const lineCtx = document.getElementById('wasteLineChart').getContext('2d');
                new Chart(lineCtx, {
                    type: 'line',
                    data: lineData
                });
            })
            .catch(error => console.error('Error fetching waste data:', error));
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
