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

    if (isset($_GET['issue_ID'])) {
        $issue_id = $_GET['issue_ID'];

        $stmt = $conn->prepare("SELECT * FROM report_issue WHERE issue_ID = ?");
        $stmt->bind_param("i", $issue_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $issue = $result->fetch_assoc();

        if (!$issue) {
            echo "<script> alert('Issue not.');
                        window.history.back();
            </script>";  
        } 
    } else {
        echo "<script> alert('No issue ID provided.');
                        window.history.back();
            </script>";        
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">

    <style>
        
    </style>
</head>
<body class="mybg">
    <div class="container details-container">
        <!-- Back Button Positioned at the Top Left -->
        <a href="javascript:history.back()" class="btn btn-back btn-success">Back to list</a>
        
        <div class="details-box">
            <h1 class="subtitle">Issue Details (ID: <?= ($issue['issue_ID']) ?>)</h1>

            <div class="row details-row">
                <div class="col-12 col-md-6"><strong>Type:</strong></div>
                <div class="col-12 col-md-6"><?= ($issue['issue_type']) ?></div>
            </div>

            <div class="row details-row">
                <div class="col-12 col-md-6"><strong>Location:</strong></div>
                <div class="col-12 col-md-6"><?= ($issue['issue_location']) ?></div>
            </div>

            <div class="row details-row">
                <div class="col-12 col-md-6"><strong>Description:</strong></div>
                <div class="col-12 col-md-6"><?= ($issue['issue_description']) ?></div>
            </div>

            <div class="row details-row">
                <div class="col-12 col-md-6"><strong>Status:</strong></div>
                <div class="col-12 col-md-6"><?= ($issue['issue_status']) ?></div>
            </div>

            <div class="row details-row">
                <div class="col-12 col-md-6"><strong>Comment:</strong></div>
                <div class="col-12 col-md-6"><?= $issue['issue_comment'] ?: 'No comment' ?></div>
            </div>

            <div class="row details-row">
                <div class="col-12 col-md-6"><strong>Photo:</strong></div>
                <div class="col-12 col-md-6">
                    <?php if (!empty($issue['issue_photo'])): ?>
                        <img src="uploads/<?= ($issue['issue_photo']) ?>" alt="Issue Photo" width="200" class="img-fluid mb-3">
                    <?php else: ?>
                        <p>No photo available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
</body>
</html>
