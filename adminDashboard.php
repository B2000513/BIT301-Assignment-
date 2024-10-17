<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: LoginNRegister.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM Users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
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
                <i class="fa fa-home"></i><span class="menu-item">Homepage</span>
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
            <a href="pickUpLog.php" class="btn btn-success">Manage Schedule Pickup</a>
            <a href="ManageIssue.php" class="btn btn-success">Manage Issue</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>