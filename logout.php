<?php
    session_start();

    $_SESSION = array();

    session_destroy();

    echo "<script> alert('You have been logged out!') </script>";

    echo "<script> window.location = 'LoginNRegister.php' </script>";

    exit();
?>
