<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: loginPlusRegistration.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pickup_date = $_POST['pickup_date'];
    $pickup_time = $_POST['pickup_time'];

    $user_id = $_SESSION['user_id'];

    // Insert pickup schedule
    $sql = "INSERT INTO Pickup_Schedule (user_id, pickup_date, pickup_time) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $pickup_date, $pickup_time);
    $stmt->execute();

    echo "<div class='alert alert-success'>Pickup scheduled successfully!</div>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Pickup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2 class="text-center">Schedule Waste Pickup</h2>
        <form method="post" action="schedule_pickup.php" class="w-50 mx-auto">
            <div class="mb-3">
                <label for="pickup_date" class="form-label">Pickup Date</label>
                <input type="date" class="form-control" name="pickup_date" required>
            </div>
            <div class="mb-3">
                <label for="pickup_time" class="form-label">Pickup Time</label>
                <input type="time" class="form-control" name="pickup_time" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Schedule Pickup</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
