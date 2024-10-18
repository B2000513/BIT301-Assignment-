<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$sql = "SELECT * FROM Users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch the schedules for the user's community
$comID = $user['ComID']; // Assuming 'ComID' is the user's community ID

$scheduleQuery = "SELECT * FROM schedules WHERE comID = ?";
$scheduleStmt = $conn->prepare($scheduleQuery);
$scheduleStmt->bind_param("i", $comID);
$scheduleStmt->execute();
$schedulesResult = $scheduleStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="sidebar">
        <button id="toggleBtn" class="btn mb-3 toggle-btn" onclick="toggleSidebar()">
            <i style="color:black;" class="fa fa-bars"></i>
        </button>
        <ul class="menu-list">
            <li>
                <i class="fa fa-home"></i><a class="nav-link" href="dashboard.php"><span class="menu-item">Homepage</span></a>
            </li>
            <li>
                <i class="fa fa-user-circle-o"></i> <a class="nav-link" href="manage_profile.php"> <span class="menu-item">Your Account</span></a>
            </li>
            <li>
                <i class="fa fa-bell"></i> <a class="nav-link" href="#"> <span class="menu-item">Announcement</span></a>
            </li>
            <li>
                <i class="fa fa-calendar-check-o"></i> <a class="nav-link" href="schedule_pickup.php"> <span class="menu-item">Schedule Pickup</span></a>
            </li>
            <li>
                <i class="fa fa-file-text"></i> <a class="nav-link" href="issue.php"><span class="menu-item">Raise Issues</span></a>
            </li>
            <li>
                <i class="fa fa-bar-chart"></i> <a class="nav-link" href="#"> <span class="menu-item">Statistics</span></a>
            </li>
            <li>
                <i class="fa fa-history"></i> <a class="nav-link" href="#"> <span class="menu-item">Your History</span></a>
            </li>
            <li>
                <i class="fa fa-sign-out"></i> <a class="nav-link" href="logout.php"> <span class="menu-item">Logout</span> </a>
            </li>
        </ul>
    </div>  

    <div class="container mt-5">
        <h1 class="text-center">Welcome, <?php echo $user['full_name']; ?>!</h1>
        <div class="text-center mt-4">
            <a href="schedule_pickup.php" class="btn btn-success">Schedule Waste Pickup</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>

        <!-- Schedules Section -->
        <div class="mt-5">
            <h2 class="text-center">Your Community's Pickup Schedules</h2>
            <?php if ($schedulesResult->num_rows > 0): ?>
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Day of the Week</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($schedule = $schedulesResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($schedule['day_of_week']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['time']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center">No schedules available for your community.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
