<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: loginPlusRegistration.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Issue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Report a Waste Management Issue</h1>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div id="status-message" class="alert d-none"></div> <!-- Status Message -->

                <form id="reportIssueForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="issue_type" class="form-label">Issue Type</label>
                        <select id="issue_type" name="issue_type" class="form-control" required>
                            <option value="">-- Select Issue Type --</option>
                            <option value="Missed Pickup">Missed Pickup</option>
                            <option value="Overflowing Bin">Overflowing Bin</option>
                            <option value="Illegal Dumping">Illegal Dumping</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" id="location" name="location" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="photo" class="form-label">Upload Photo (Optional)</label>
                        <input type="file" id="photo" name="photo" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Issue</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    $('#reportIssueForm').on('submit', function(event) {
        event.preventDefault();  // Prevent default form submission

        var formData = new FormData(this);

        $.ajax({
            url: 'api/report.php',  // API endpoint
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                // Log the response for debugging
                console.log('Success Response:', response);

                // Display success banner
                $('#status-message')
                    .removeClass('d-none alert-danger')  // Remove error styling if present
                    .addClass('alert-success')           // Add success styling
                    .text('Issue successfully reported. Issue ID: ' + response.issue_id);
            },
            error: function(xhr) {
                // Log the error for debugging
                console.log('Error Response:', xhr);

                var errorResponse = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred. Please try again.';
                
                // Display error banner
                $('#status-message')
                    .removeClass('d-none alert-success')  // Remove success styling if present
                    .addClass('alert-danger')             // Add error styling
                    .text(errorResponse);
            }
        });
    });
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
