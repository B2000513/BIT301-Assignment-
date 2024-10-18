<?php
session_start();
include 'db.php';  // MySQLi connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$successMsg = $errorMsg = '';  // Initialize feedback messages

// Fetch current user's profile
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Ensure the logged-in user has a role
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Check if the 'ComID' key is set in the session or use the selected ComID from the dropdown
$adminComID = isset($_POST['selected_com_id']) ? $_POST['selected_com_id'] : (isset($_SESSION['ComID']) ? $_SESSION['ComID'] : null);

// Fetch all communities for dropdowns
$communityQuery = "SELECT * FROM community";
$communityResult = $conn->query($communityQuery);

// Fetch available days of the week from the database
$daysOfWeekQuery = "SELECT  day_of_week FROM schedules";
$daysOfWeekResult = $conn->query($daysOfWeekQuery);

// Predefined days of the week (Monday through Friday)
$defaultDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

// Collect dynamic days from the database
$dynamicDays = [];
if ($daysOfWeekResult->num_rows > 0) {
    while ($row = $daysOfWeekResult->fetch_assoc()) {
        $dynamicDays[] = $row['day_of_week'];
    }
}

// Combine predefined days with dynamic days
$allDaysOfWeek = array_unique(array_merge($defaultDays, $dynamicDays));

// Fetch schedules for the current admin's community
if ($adminComID) {
    $scheduleQuery = "SELECT * FROM schedules WHERE comID = ?";
    $scheduleStmt = $conn->prepare($scheduleQuery);
    $scheduleStmt->bind_param('i', $adminComID);
    $scheduleStmt->execute();
    $schedulesResult = $scheduleStmt->get_result();
}


// Fetch schedules for the current admin's community
if ($adminComID) {
    $scheduleQuery = "SELECT * FROM schedules WHERE comID = ?";
    $scheduleStmt = $conn->prepare($scheduleQuery);
    $scheduleStmt->bind_param('i', $adminComID);
    $scheduleStmt->execute();
    $schedulesResult = $scheduleStmt->get_result();
}

// Handle schedule form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedules'])) {
    $dayOfWeek = $_POST['day_of_week'];
    $startTime = $_POST['startTime'];
    $endTime = $_POST['endTime'];

    // Validate inputs
    if (empty($dayOfWeek) || empty($startTime) || empty($endTime)) {
        $errorMsg = "All fields are required for schedules.";
    } else {
        // Check if the schedule already exists for the selected day
        $existingScheduleQuery = "SELECT * FROM schedules WHERE comID = ? AND day_of_week = ?";
        $existingScheduleStmt = $conn->prepare($existingScheduleQuery);
        $existingScheduleStmt->bind_param('is', $adminComID, $dayOfWeek);
        $existingScheduleStmt->execute();
        $existingScheduleResult = $existingScheduleStmt->get_result();

        if ($existingScheduleResult->num_rows > 0) {
            // Update existing schedule
            $updateScheduleQuery = "UPDATE schedules SET time = ?, endTime = ? WHERE comID = ? AND day_of_week = ?";
            $updateScheduleStmt = $conn->prepare($updateScheduleQuery);
            $updateScheduleStmt->bind_param('ssis', $startTime, $endTime, $adminComID, $dayOfWeek);

            if ($updateScheduleStmt->execute()) {
                $successMsg = "Schedule updated successfully!";
            } else {
                $errorMsg = "Failed to update schedule. Please try again.";
            }
        } else {
            // Insert a new schedule
            $insertScheduleQuery = "INSERT INTO schedules (comID, day_of_week, time, endTime) VALUES (?, ?, ?, ?)";
            $insertScheduleStmt = $conn->prepare($insertScheduleQuery);
            $insertScheduleStmt->bind_param('isss', $adminComID, $dayOfWeek, $startTime, $endTime);

            if ($insertScheduleStmt->execute()) {
                $successMsg = "Schedule added successfully!";
            } else {
                $errorMsg = "Failed to add schedule. Please try again.";
            }
        }
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = $_POST['full_name'];
    $address = $_POST['address'];
    $phoneNumber = $_POST['phone_number'];
    $newComID = $_POST['com_id'];

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $updateQuery = "UPDATE users SET full_name = ?, address = ?, password = ?, phone_number = ?, ComID = ? WHERE user_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param('sssii', $fullName, $address, $password, $phoneNumber, $newComID, $userId);
    } else {
        $updateQuery = "UPDATE users SET full_name = ?, address = ?, phone_number = ?, ComID = ? WHERE user_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param('sssii', $fullName, $address, $phoneNumber, $newComID, $userId);
    }

    if ($updateStmt->execute()) {
        $successMsg = "Profile updated successfully!";
    } else {
        $errorMsg = "Failed to update profile. Please try again.";
    }
}

// Admin: Fetch users for the selected or default community
if ($isAdmin && $adminComID !== null) {
    $adminQuery = "SELECT * FROM users WHERE ComID = ?";
    $adminStmt = $conn->prepare($adminQuery);
    $adminStmt->bind_param('i', $adminComID);
    $adminStmt->execute();
    $allUsers = $adminStmt->get_result();

    // Update user's community by admin
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user_comid'])) {
        $userToUpdate = $_POST['user_id'];
        $newComID = $_POST['new_com_id'];

        $updateComIDQuery = "UPDATE users SET ComID = ? WHERE user_id = ?";
        $updateComIDStmt = $conn->prepare($updateComIDQuery);
        $updateComIDStmt->bind_param('ii', $newComID, $userToUpdate);

        if ($updateComIDStmt->execute()) {
            $successMsg = "User's community updated successfully!";
        } else {
            $errorMsg = "Failed to update user's community. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile & Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
</head>

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

<body class="container py-4">

    <h1 class="mb-4">Manage Your Profile</h1>

    <!-- Display feedback messages -->
    <?php if ($successMsg): ?>
        <div class="alert alert-success" role="alert">
            <?= htmlspecialchars($successMsg) ?>
        </div>
    <?php elseif ($errorMsg): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($errorMsg) ?>
        </div>
    <?php endif; ?>

    <!-- Profile Update Form -->
    <form method="POST" action="" class="mb-5">
        <div class="mb-3">
            <label for="full_name" class="form-label">Full Name:</label>
            <input type="text" name="full_name" id="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password (leave blank to keep current):</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="New Password (if changing)">
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Address:</label>
            <input type="text" name="address" id="address" class="form-control" value="<?= htmlspecialchars($user['address']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="phone_number" class="form-label">Phone Number:</label>
            <input type="text" name="phone_number" id="phone_number" class="form-control" value="<?= htmlspecialchars($user['phone_number']) ?>">
        </div>
        <div class="mb-3">
            <label for="com_id" class="form-label">Community:</label>
            <select name="com_id" id="com_id" class="form-control" required>
                <?php while ($community = $communityResult->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($community['ComID']) ?>" <?= ($community['ComID'] == $user['ComID']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($community['ComName']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
    </form>

    <!-- Admin Section: Manage Schedules -->
    <?php if ($isAdmin): ?>
        <h2 class="mb-3">Manage Time Schedules (Admin Only)</h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="day_of_week" class="form-label">Day of Week:</label>
                <select name="day_of_week" id="day_of_week" class="form-control" required>
                <?php foreach ($allDaysOfWeek as $day): ?>
                        <option value="<?= htmlspecialchars($day) ?>"><?= htmlspecialchars($day) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="startTime" class="form-label">Start Time:</label>
                <input type="time" name="startTime" id="startTime" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="endTime" class="form-label">End Time:</label>
                <input type="time" name="endTime" id="endTime" class="form-control" required>
            </div>
            <button type="submit" name="schedules" class="btn btn-primary">Add/Update Schedule</button>
        </form>

        <!-- Display Schedules for the Selected Community -->
        <h2 class="mt-5 mb-3">Schedules for Community <?= $adminComID ?></h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Day of Week</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($schedule = $schedulesResult->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($schedule['day_of_week']) ?></td>
                        <td><?= htmlspecialchars($schedule['time']) ?></td>
                        <td><?= htmlspecialchars($schedule['endTime']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Manage Users Section -->
        <h2 class="mb-3">Manage Community Users (Admin Only)</h2>
        

        <!-- Display Users for the Selected Community -->
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Community</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $allUsers->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['user_id']) ?></td>
                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td>
                            <form method="POST" action="">
                                <select name="new_com_id" class="form-control">
                                    <?php
                                    $communityResult->data_seek(0); // Reset pointer to fetch communities again
                                    while ($community = $communityResult->fetch_assoc()): ?>
                                        <option value="<?= htmlspecialchars($community['ComID']) ?>" <?= ($community['ComID'] == $user['ComID']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($community['ComName']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                <button type="submit" name="update_user_comid" class="btn btn-sm btn-primary mt-2">Update Community</button>
                            </form>
                        </td>
                        <td>
                            <a href="edit_user.php?user_id=<?= $user['user_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="delete_user.php?user_id=<?= $user['user_id'] ?>" class="btn btn-sm btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="script.js"></script>
</body>

</html>
