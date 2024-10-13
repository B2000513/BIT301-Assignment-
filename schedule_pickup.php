<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: loginPlusRegistration.php');
    exit();
}

$waste_types = $conn->query("SELECT * FROM Waste_Types");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pickup_date = $_POST['pickup_date'];
    $pickup_time = $_POST['pickup_time'];
    $waste_type_ids = $_POST['waste_type'];

    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO Pickup_Schedule (user_id, pickup_date, pickup_time) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $pickup_date, $pickup_time);
    $stmt->execute();

    $pickup_id = $stmt->insert_id;

    foreach ($waste_type_ids as $waste_type_id) {
        $sql = "INSERT INTO Scheduled_Waste (pickup_id, waste_type_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $pickup_id, $waste_type_id);
        $stmt->execute();
    }

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

            <div class="mb-3">
                <label for="waste_type" class="form-label">Select Waste Type</label><br>
                <?php while ($row = $waste_types->fetch_assoc()): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="waste_type[]" value="<?php echo $row['waste_type_id']; ?>" id="waste_<?php echo $row['waste_type_id']; ?>">
                        <label class="form-check-label" for="waste_<?php echo $row['waste_type_id']; ?>">
                            <?php echo $row['waste_type_name']; ?>
                        </label>
                    </div>
                <?php endwhile; ?>
            </div>

            <button type="submit" class="btn btn-primary w-100">Schedule Pickup</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>