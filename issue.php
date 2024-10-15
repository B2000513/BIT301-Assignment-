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

    date_default_timezone_set('Asia/Kuala_Lumpur');

    $timestamp = date('dmy_His');

    $query = "SELECT * FROM report_issue ORDER BY issue_ID DESC LIMIT 1";
    $result = $conn->query($query);

    if ($result) {
        $lastRow = $result->fetch_assoc(); 
        $lastID = $lastRow['issue_ID'] + 1;
    } else {
        $lastID = 0;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $errors = [];

        $issue_type = $_POST['issue_type'];
        $issue_description = $_POST['issue_description'];
        $issue_location = $_POST['issue_location'];
        $issue_comment = $_POST['issue_comment'];
        $user_id = $_SESSION['user_id'];

        $photo_path = null;
        
        if(empty($issue_type)){
            $errors[] = "Please select the type of issue.";
        }

        if (!empty($errors)) {
            echo "<script>alert('" . implode("\\n", $errors) . "');</script>";
            echo "<script>window.history.back();</script>";
            exit();
        }

        if (isset($_FILES['issue_photo'])) {
            $issue_photo = $_FILES['issue_photo'];
        
            if ($issue_photo['error'] !== UPLOAD_ERR_OK) {
                echo "Error uploading file: " . $issue_photo['error']; 
                exit;
            }
        
            $target_dir = "uploads/"; 
            if (!is_dir($target_dir)) {
                echo "Upload directory does not exist.";
            }
        

            $filename = preg_replace('/\s+/', '_', $issue_photo['name']); 
            $target_file = $target_dir . basename($filename); 
        
            // if (move_uploaded_file($issue_photo['tmp_name'], $target_file)) {
            //     $photo_path = $target_file; 
            //     $photo_name = $timestamp.'_'.$lastID.'_'.$photo_path;
            // } else {
            //     echo "Error moving the uploaded photo.";
            //     exit;
            // }
            if (move_uploaded_file($issue_photo['tmp_name'], $target_file)) {
                // Generate a unique photo name
                $filename = preg_replace('/\s+/', '_', pathinfo($issue_photo['name'], PATHINFO_FILENAME)); // Get filename without extension
                $extension = pathinfo($issue_photo['name'], PATHINFO_EXTENSION); // Get the file extension
                $photo_name = $timestamp . '_' . $lastID . '_' . $filename . '.' . $extension; // New unique filename
            
                // Move the uploaded file to the target directory with the new name
                if (rename($target_file, $target_dir . $photo_name)) {
                    $photo_path = $target_dir . $photo_name; // Store the full path for database insertion
                } else {
                    echo "Error renaming the uploaded photo.";
                    exit;
                }
            }
            
        } else {
            echo "No photo uploaded or there was an upload error.";
            exit;
        }
        
        $sqlQuery = "INSERT INTO report_issue (issue_type, issue_location, issue_description, issue_comment, issue_photo, issue_status, user_id) VALUES (?, ?, ?, ?, ?, 'Pending', ?)";
        $stmt = $conn->prepare($sqlQuery);
        $stmt->bind_param("sssssi", $issue_type, $issue_location, $issue_description, $issue_comment, $photo_name, $user_id); // Bind parameters
    
        $ret = $stmt->execute();

        if ($ret == TRUE) {
            echo "<script>alert('This issue has been reported!')</script>";
            echo "<script>window.location = 'dashboard.php'</script>";
        } else {
            echo "Fail to report the issue: " . $stmt->error; 
        }

    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Issue</title>
    <link rel="icon" type="image/x-icon" href="image/issue.png">
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

    <div class="text-center"> 
        <h1 class="title"> Wastex </h1>
        <p class="title"> Your Best Recycle Helper</p>
    </div> 

    <div class="container mt-3 form-border">
        <h2 class="text-center">Report Issues</h2>
        <form method="post" action="issue.php" class="w-50 mx-auto" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="issue_type" class="form-label">Type of issue:</label> <br>
                <select class="form-select" id="issue_type" name="issue_type">
                    <option value="" selected disabled>Select a type</option>
                    <option value="missed_pickup">Missed pickup</option>
                    <option value="overflowing_bin">Overflowing bin</option>
                    <option value="illegal_dumping">Illegal dumping</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="issue_location" class="form-label">Location:</label>
                <input type="text" class="form-control" name="issue_location" required>
            </div>

            <div class="mb-3">
                <label for="issue_description" class="form-label">Description:</label>
                <input type="text" class="form-control" name="issue_description" required>
            </div>

            <div class="mb-3">
                <label for="issue_comment" class="form-label">Comment:</label>
                <input type="text" class="form-control" name="issue_comment">
            </div>

            <div class="mb-3">
                <label style="padding-bottom:10px;" for="photo">Photo:</label> <br>
                <input type="file" id="issue_photo" name="issue_photo">
            </div>

            <br>

            <button type="submit" class="btn btn-submit btn-success w-100">Report Issue</button>
        </form>
    </div>    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>

</html>
