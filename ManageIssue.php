    <?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    include 'db.php'; 
    session_start();

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: LoginNRegister.php');
        exit();
    }

    // Fetch issues from the database
    $sql = "SELECT issue_ID, issue_type, issue_location, issue_status, issue_photo FROM report_issue";
    $result = $conn->query($sql);

    // Process the form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $issue_IDs = isset($_POST['issue_ID']) ? $_POST['issue_ID'] : [];
        $bulk_status = isset($_POST['bulk_status']) ? $_POST['bulk_status'] : '';

        // Ensure at least one issue is selected
        if (!empty($issue_IDs) && !empty($bulk_status)) {
            $cannotUpdate = []; // Array to hold issues that cannot be updated

            foreach ($issue_IDs as $issue_ID) {
                // Fetch current status of the issue
                $stmt = $conn->prepare("SELECT issue_status FROM report_issue WHERE issue_ID = ?");
                $stmt->bind_param("i", $issue_ID);
                $stmt->execute();
                $stmt->bind_result($current_status);
                $stmt->fetch();
                $stmt->close();

                // Check if the current status is "Processing" and prevent change back to "Pending"
                if ($current_status === "Processing" && $bulk_status === "Pending") {
                    $cannotUpdate[] = $issue_ID; // Add to the list of issues that cannot be updated
                    continue; // Skip this issue
                }

                // Update the status
                $update_stmt = $conn->prepare("UPDATE report_issue SET issue_status = ? WHERE issue_ID = ?");
                $update_stmt->bind_param("si", $bulk_status, $issue_ID);
                $update_stmt->execute();
                $update_stmt->close();
            }

            // Redirect to the same page to refresh and show a message if there are blocked updates
            if (!empty($cannotUpdate)) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?error=" . urlencode(implode(", ", $cannotUpdate)));
                exit();
            } else {
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        } else {
            // Display an error message if no issues are selected
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
                const status = checkbox.getAttribute('data-status'); // Get the status from data attribute
                if (status === 'Completed') {
                    checkbox.disabled = true; // Disable the checkbox
                }
            });
        }

        window.onload = disableCheckboxes; // Call the function on page load

        function confirmSubmit() {
            const selectedCheckboxes = document.querySelectorAll('input[name="issue_ID[]"]:checked');
            if (selectedCheckboxes.length === 0) {
                alert("Please select at least one issue to update.");
                return false; // Prevent form submission
            }
            return confirm("Are you sure you want to update the selected issues?");
        }

        // Show error message if there are issues that cannot be updated
        <?php if (isset($_GET['error'])): ?>
            alert("Cannot change status to Pending for issue(s): <?= ($_GET['error']) ?>. These issues are currently in Processing status.");
        <?php endif; ?>
    </script>
    
</body>
</html>
