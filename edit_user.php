<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: manage_profile.php');
    exit();
}

include 'db.php';  // MySQLi connection

// Get the user ID to be edited and the admin's ComID
$editUserId = $_GET['user_id'];
$adminComID = $_SESSION['ComID'];

// Fetch the user data to be edited
$query = "SELECT * FROM users WHERE user_id = ? AND ComID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $editUserId, $adminComID);
$stmt->execute();
$result = $stmt->get_result();
$editUser = $result->fetch_assoc();

if (!$editUser) {
    echo "You are not allowed to edit this user.";
    exit();
}

// Fetch all communities for the dropdown
$communityQuery = "SELECT * FROM community";
$communityResult = $conn->query($communityQuery);

// Update user logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'];
    $role = $_POST['role'];
    $newComID = $_POST['com_id'];  // Get the selected community ID

    $updateQuery = "UPDATE users SET full_name = ?, role = ?, ComID = ? WHERE user_id = ? AND ComID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('ssiii', $fullName, $role, $newComID, $editUserId, $adminComID);
    $stmt->execute();

    header('Location: manage_profile.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">

    <h1 class="mb-4">Edit User Profile</h1>

    <!-- Edit User Form -->
    <form method="POST" action="">
        <div class="mb-3">
            <label for="full_name" class="form-label">Full Name:</label>
            <input type="text" name="full_name" id="full_name" class="form-control" value="<?= htmlspecialchars($editUser['full_name']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Role:</label>
            <input type="text" name="role" id="role" class="form-control" value="<?= htmlspecialchars($editUser['role']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="com_id" class="form-label">Community:</label>
            <select name="com_id" id="com_id" class="form-control" required>
                <?php while ($community = $communityResult->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($community['ComID']) ?>" <?= ($community['ComID'] == $editUser['ComID']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($community['ComName']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update User</button>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</body>
</html>
