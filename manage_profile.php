<?php
session_start();
include 'db.php';  // MySQLi connection

// Fetch current user's profile
$userId = $_SESSION['user_id'];

// Check if the 'ComID' key is set in the session, use a fallback or handle accordingly
$adminComID = isset($_SESSION['ComID']) ? $_SESSION['ComID'] : null;

// Prepare the query to fetch user data
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if the logged-in user is an admin
$isAdmin = ($_SESSION['role'] === 'admin');

// Fetch all communities for the dropdown
$communityQuery = "SELECT * FROM community";
$communityResult = $conn->query($communityQuery);

// Initialize feedback message variables
$successMsg = $errorMsg = '';

// Update Profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = $_POST['full_name'];
    $address = $_POST['address'];
    $phoneNumber = $_POST['phone_number'];
    $newComID = $_POST['com_id'];  // Get selected community ID

    // Update user profile in the database
    $updateQuery = "UPDATE users SET full_name = ?, address = ?, phone_number = ?, ComID = ? WHERE user_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param('sssii', $fullName, $address, $phoneNumber, $newComID, $userId);

    // Execute the update query and check for success
    if ($updateStmt->execute()) {
        $successMsg = "Profile updated successfully!";
    } else {
        $errorMsg = "Failed to update profile. Please try again.";
    }
}

// Admin: Fetch all users within the same ComID
if ($isAdmin && $adminComID !== null) {  // Ensure $adminComID is set
    $adminQuery = "SELECT * FROM users WHERE ComID = ?";
    $adminStmt = $conn->prepare($adminQuery);
    $adminStmt->bind_param('i', $adminComID);
    $adminStmt->execute();
    $allUsers = $adminStmt->get_result();

    // Update user's community by admin
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user_comid'])) {
        $userToUpdate = $_POST['user_id'];
        $newComID = $_POST['new_com_id'];

        // Update user's community
        $updateComIDQuery = "UPDATE users SET ComID = ? WHERE user_id = ?";
        $updateComIDStmt = $conn->prepare($updateComIDQuery);
        $updateComIDStmt->bind_param('ii', $newComID, $userToUpdate);

        // Provide feedback to the admin
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
    <title>Manage Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container py-4">

    <h1 class="mb-4">Manage Your Profile</h1>

    <!-- Display feedback messages -->
    <?php if ($successMsg): ?>
        <div class="alert alert-success" role="alert">
            <?= htmlspecialchars($successMsg) ?>
        </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
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

    <!-- Admin Section: Manage Other Users -->
    <?php if ($isAdmin): ?>
        <h2 class="mb-3">Manage Other Users (Admin Only)</h2>
        <table class="table table-bordered">
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

</body>

</html>