<?php
    session_start();
    include 'db.php';

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    require 'Mail/vendor/autoload.php';


    if (isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
    
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc(); // Fetch user as an associative array
    
            // Verify the password
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];
                if ($user['email_verified_at'] == null) {
                    echo "<div class='alert alert-warning'>Please verify your email from <a href='email-verification.php?email=" . htmlspecialchars($email) . "'>here</a>.</div>";
                } else {
                    if($user['role'] == 'personal'){
                        echo "<script> alert('You have logged in as community user!') </script>";
                        echo "<script> window.location = 'dashboard.php' </script>";
                        exit;
                    } else if($user['role'] == 'admin'){
                        echo "<script> alert('You have logged in as community admin!') </script>";
                        echo "<script> window.location = 'ManageIssue.php' </script>";
                        exit;
                    }
                }
            } else {
                echo "<div class='alert alert-danger'>Invalid password!</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>User not found!</div>";
        }
    }

    if (isset($_POST["register"]))
    {
        $email = $_POST['reg_email'];
        $password = $_POST['reg_password'];
        $full_name = $_POST['reg_full_name'];
        $address = $_POST['reg_address'];
        $phone_number = $_POST['reg_phone_number'];
        $community_id = $_POST['community_id'];

        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<div class='alert alert-danger'>The email already exists!</div>";
        } else {
            $mail = new PHPMailer(true);

            try {
                $mail->SMTPDebug = 0;

                $mail->isSMTP();

                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'xwkhor0713@gmail.com';
                $mail->Password = 'wfzg mztw sfda cqhs';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('wastex.cwms@gmail.com', 'Wastex - CWMS');
                $mail->addAddress($email, $full_name);
                $mail->isHTML(true);

                $verification_code = substr(number_format(time() * rand(), 0, '', ''), 0, 6);

                $mail->Subject = 'Email verification';
                $mail->Body    = '<p>Your verification code for CWMS is: <b>' . $verification_code . '</b></p>';

                $mail->send();

                $encrypted_password = password_hash($password, PASSWORD_DEFAULT);

                // Prepare the SQL statement
                $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, address, phone_number, verification_code, email_verified_at, role, ComID) VALUES (?, ?, ?, ?, ?, ?, NULL, 'personal',?)");

                $stmt->bind_param("sssssii", $full_name, $email, $encrypted_password, $address, $phone_number, $verification_code, $community_id);

                if ($stmt->execute()) {
                    echo "<div class='alert alert-success'>Registration successful! You will receive the authentication code in your email.</div>";
                } else {
                    echo "Error: " . $stmt->error; // Output error if the query fails
                }

                // Close the statement
                $stmt->close();

                header("Location: email-verification.php?email=" . $email);
                exit();

            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body class="mybg">
    <div class="container mt-5">
        <h2 class="text-center">Login or Register</h2>
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab" aria-controls="login" aria-selected="true">Login</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab" aria-controls="register" aria-selected="false">Register</button>
            </li>
        </ul>

        <div class="tab-content mt-3" id="myTabContent">
            <!-- Login Form -->
            <div class="tab-pane fade show active" id="login" role="tabpanel" aria-labelledby="login-tab">
                <form method="post" action="" class="w-50 mx-auto">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="text" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-submit btn-success w-100">Login</button>
                </form>
            </div>

            <!-- Register Form -->
            <div class="tab-pane fade" id="register" role="tabpanel" aria-labelledby="register-tab">
                <form method="post" action="" class="w-50 mx-auto">
                    <div class="mb-3">
                        <label for="reg_email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="reg_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="reg_password" class="form-label">Password</label>
                        <input type="password" class="form-control" name="reg_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="reg_full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="reg_full_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="reg_address" class="form-label">Address</label>
                        <input type="text" class="form-control" name="reg_address" required>
                    </div>
                    <div class="mb-3">
                        <label for="reg_phone_number" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" name="reg_phone_number">
                    </div>
                    <div class="mb-3">
                        <label for="community_id" class="form-label">Community</label>
                        <select class="form-select" name="community_id" required>
                            <option value="">Select a Community</option>
                            <?php
                            // Assuming you have a database connection established
                            $query = "SELECT ComID, ComName FROM community"; // Adjust the query as per your table structure
                            $result = mysqli_query($conn, $query);

                            while ($row = mysqli_fetch_assoc($result)) {
                                echo '<option value="' . $row['ComID'] . '">' . $row['ComName'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" name="register" class="btn btn-submit btn-success w-100">Register</button>
                </form>
            </div>
        </div>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    
</body>

</html>
