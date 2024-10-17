<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    include 'db.php'; 
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header('Location: LoginNRegister.php');
        exit();
    }

    if ($_SESSION['role'] != 'admin') {
        echo "<script>
            alert('Access denied. Only admins can access this page.');
            window.history.back();
        </script>";
    }

    $adminComID = $_SESSION['ComID']; 

    $sql = "SELECT ri.issue_ID, ri.issue_type, ri.issue_location, ri.issue_status, ri.issue_photo 
        FROM report_issue ri
        INNER JOIN users u ON ri.user_id = u.user_id
        WHERE u.ComID = ?"; 

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $adminComID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $issue_IDs = isset($_POST['issue_ID']) ? $_POST['issue_ID'] : [];
        $bulk_status = isset($_POST['bulk_status']) ? $_POST['bulk_status'] : '';

        if (!empty($issue_IDs) && !empty($bulk_status)) {
            $cannotUpdate = []; 

            foreach ($issue_IDs as $issue_ID) {
                $stmt = $conn->prepare("SELECT issue_status FROM report_issue WHERE issue_ID = ?");
                $stmt->bind_param("i", $issue_ID);
                $stmt->execute();
                $stmt->bind_result($current_status);
                $stmt->fetch();
                $stmt->close();

                if ($current_status === "Processing" && $bulk_status === "Pending") {
                    $cannotUpdate[] = $issue_ID; 
                    continue; 
                }

                $update_stmt = $conn->prepare("UPDATE report_issue SET issue_status = ? WHERE issue_ID = ?");
                $update_stmt->bind_param("si", $bulk_status, $issue_ID);
                $update_stmt->execute();
                $update_stmt->close();
            }

            if (!empty($cannotUpdate)) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?error=" . urlencode(implode(", ", $cannotUpdate)));
                exit();
            } else {
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        } else {
            echo "<script>alert('Please select at least one issue and a status.');</script>";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Issue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="mybg">

    <div class="sidebar">
        <button id="toggleBtn" class="btn mb-3 toggle-btn" onclick="toggleSidebar()">
            <i style="color:black;" class="fa fa-bars"></i>
        </button>
        <ul class="menu-list">
            <li>
                <i class="fa fa-home"></i><span class="menu-item">Homepage</span>
            </li>
            <li>
                <i class="fa fa-user-circle-o"></i> <a class="nav-link" href="#"> <span class="menu-item">Your Account</span></a>
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

    <div class="container">
        <h1 class="mt-4 text-center" style="padding-bottom:20px;">Manage Issues</h1>
        <form id="issue_form" action="" method="POST" onsubmit="return confirmSubmit()">
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Select</th>
                        <th>Issue ID</th>
                        <th>Issue Type</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Photo</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><input type="checkbox" name="issue_ID[]" value="<?= $row['issue_ID'] ?>" data-status="<?= $row['issue_status'] ?>"></td>
                        <td><?= $row['issue_ID'] ?></td>
                        <td><?= $row['issue_type'] ?></td>
                        <td><?= $row['issue_location'] ?></td>
                        <td><?= $row['issue_status'] ?></td>
                        <td>
                            <?php if (!empty($row['issue_photo'])): ?>
                                <img src="uploads/<?= $row['issue_photo'] ?>" alt="Issue Photo" width="100">
                            <?php else: ?>
                                No photo
                            <?php endif; ?>
                        </td>
                        <td><button type="button" class="btn btn-success" onclick="viewDetails(<?= $row['issue_ID'] ?>)">View Details</button></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="text-center">
                <label for="bulk_status">Change Status for Selected Issues:</label>
                <select id="bulk_status" name="bulk_status" required>
                    <option value="">Select Status</option>
                    <option value="Processing">Processing</option>
                    <option value="Completed">Completed</option>
                </select><br><br>
            </div>

            <div class="col-md-12 text-center"> 
                <button type="submit" class="btn btn-success">Update Selected Issues</button>
            </div>
        </form>
    </div>

    <script>
        function viewDetails(issueID) {
            window.location.href = `/BIT301_Assignment_main/ViewIssue.php?issue_ID=${issueID}`;
        }

        function disableCheckboxes() {
            const checkboxes = document.querySelectorAll('input[name="issue_ID[]"]');
            checkboxes.forEach(checkbox => {
                const status = checkbox.getAttribute('data-status'); 
                if (status === 'Completed') {
                    checkbox.disabled = true; 
                }
            });
        }

        window.onload = disableCheckboxes; 

        function confirmSubmit() {
            const selectedCheckboxes = document.querySelectorAll('input[name="issue_ID[]"]:checked');
            if (selectedCheckboxes.length === 0) {
                alert("Please select at least one issue to update.");
                return false; 
            }
            return confirm("Are you sure you want to update the selected issues?");
        }

        <?php if (isset($_GET['error'])): ?>
            alert("Cannot change status to Pending for issue(s): <?= ($_GET['error']) ?>. These issues are currently in Processing status.");
        <?php endif; ?>
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
