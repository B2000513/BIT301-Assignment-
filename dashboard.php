<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: loginPlusRegistration.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = ?";
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
    <div class="container mt-5">
        <div class="row">
            <!-- Vertical Navigation Bar (Usecase links in separate columns) -->
            <div class="col-md-3">
                <div class="d-flex flex-column">
                    <a href="dashboard.php" class="btn btn-light mb-3 w-100">Dashboard</a>
                    <a href="schedule_pickup.php" class="btn btn-success mb-3 w-100">Schedule Pickup</a>
                    <a href="pickup_history.php" class="btn btn-primary mb-3 w-100">Pickup History</a>
                    <a href="logout.php" class="btn btn-danger w-100">Logout</a>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-md-9">
                <h1 class="text-center">Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
                <p class="text-center mt-3">
                    Use the navigation on the left to manage your waste pickup activities.
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
