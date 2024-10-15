<?php
include '../db.php';
session_start();

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php ');
    exit();
}

// Fetch user role from the session
$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT role FROM Users WHERE user_id = $user_id");
$user = $result->fetch_assoc();

if ($user['role'] !== 'admin') {
    // Redirect non-admin users to their dashboard or a forbidden page
    header('Location: dashboard.php'); // Or 'dashboard.php' for normal users
    exit();
}

// Fetch all pickup schedules for admin
$schedules = $conn->query("SELECT ps.pickup_id, ps.pickup_date, ps.pickup_time, u.full_name 
                           FROM Pickup_Schedule ps 
                           JOIN Users u ON ps.user_id = u.user_id");

// Fetch waste types
$waste_types = $conn->query("SELECT * FROM waste");

// Handle form submission for updating pickup schedule
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pickup_id'])) {
    $pickup_id = $_POST['pickup_id'];
    $pickup_date = $_POST['pickup_date'];
    $pickup_time = $_POST['pickup_time'];
    // $waste_type_ids = $_POST['wasteType'];
    $pickup_status = $_POST['pickup_status'];

    // Update the Pickup Schedule
    $sql = "UPDATE Pickup_Schedule SET pickup_date = ?, pickup_time = ?, status = ? WHERE pickup_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $pickup_date, $pickup_time, $pickup_status, $pickup_id); // Bind status as well
$stmt->execute();

    // // Delete old scheduled waste types
    // $conn->query("DELETE FROM Scheduled_Waste WHERE pickup_id = $pickup_id");

    // // Insert the new waste types
    // foreach ($waste_type_ids as $waste_type_id) {
    //     $sql = "INSERT INTO Scheduled_Waste (pickup_id, waste_type_id) VALUES (?, ?)";
    //     $stmt = $conn->prepare($sql);
    //     $stmt->bind_param("ii", $pickup_id, $waste_type_id);
    //     $stmt->execute();
    // }

    echo "<div class='alert alert-success'>Pickup updated successfully!</div>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin: Manage Pickups</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2 class="text-center">Admin: Manage Waste Pickups</h2>

        <!-- Pickup Schedule Table -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pickup Date</th>
                    <th>Pickup Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $schedules->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['pickup_id']; ?></td>
                        <td><?php echo $row['pickup_date']; ?></td>
                        <td><?php echo $row['pickup_time']; ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="editPickup(<?php echo $row['pickup_id']; ?>)">Edit</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Edit Pickup Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Pickup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="" id="editPickupForm">
                    <!-- Hidden pickup ID field -->
                    <input type="hidden" name="pickup_id" id="pickup_id">
                    
                    <!-- Pickup Date -->
                    <div class="mb-3">
                        <label for="pickup_date" class="form-label">Pickup Date</label>
                        <input type="date" class="form-control" name="pickup_date" id="pickup_date" required>
                    </div>
                    
                    <!-- Pickup Time -->
                    <div class="mb-3">
                        <label for="pickup_time" class="form-label">Pickup Time</label>
                        <input type="time" class="form-control" name="pickup_time" id="pickup_time" required>
                    </div>

                    <!-- Waste Types -->
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

                    <!-- Pickup Status (Pending, Cancelled, Confirmed, Completed) -->
                    <div class="mb-3">
                        <label for="pickup_status" class="form-label">Pickup Status</label><br>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pickup_status" value="Cancelled" id="status_cancelled" required>
                            <label class="form-check-label" for="status_cancelled">Cancelled</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pickup_status" value="Confirmed" id="status_confirmed" required>
                            <label class="form-check-label" for="status_confirmed">Confirmed</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pickup_status" value="Completed" id="status_completed" required>
                            <label class="form-check-label" for="status_completed">Completed</label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">Update Pickup</button>
                </form>
            </div>
        </div>
    </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to open the edit modal and populate it with the selected pickup details
        function editPickup(pickup_id) {
            // Fetch pickup data using AJAX or directly from PHP (if data is preloaded)

            // Example of preloading the form with pickup_id
            document.getElementById('pickup_id').value = pickup_id;

            // Open the modal
            var editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        }
    </script>
</body>

</html>
