<?php
include '../db.php';  // Adjust the path to your database connection
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Invalid request method.']);
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'User not authenticated.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Retrieve POST data
$issue_type = $_POST['issue_type'] ?? '';
$location = $_POST['location'] ?? '';
$description = $_POST['description'] ?? '';
$photo = null;

// Validate inputs
if (empty($issue_type) || empty($location) || empty($description)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Please provide all required fields (issue type, location, description).']);
    exit();
}

// Handle optional photo upload
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    // Get file extension
    $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    
    // Check if the file is a valid image type (PNG or JPG)
    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    if (in_array($file_extension, $allowed_extensions)) {
        $photo = 'uploads/' . basename($_FILES['photo']['name']);
        
        // Move the uploaded file to the uploads directory
        if (move_uploaded_file($_FILES['photo']['tmp_name'], '../' . $photo)) {
            echo "File uploaded successfully.";
        } else {
            echo "Error moving the uploaded file.";
        }
    } else {
        echo "Invalid file type. Only JPG and PNG files are allowed.";
    }
} else {
    echo "No file uploaded or there was an upload error.";
}


// Insert issue into the database
$sql = "INSERT INTO issues (user_id, issue_type, location, description, photo, status)
        VALUES (?, ?, ?, ?, ?, 'NEW')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issss", $user_id, $issue_type, $location, $description, $photo);

if ($stmt->execute()) {
    $issue_id = $stmt->insert_id;
    echo json_encode([
        'message' => 'Issue reported successfully.',
        'issue_id' => $issue_id,
        'status' => 'NEW'
    ]);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to report the issue.']);
}
