<?php
if (!isset($_POST['email'])) {
    exit();
}

include '../conn.php';
$email = strtolower(mysqli_real_escape_string($conn, $_POST['email']));
$mode = strtolower($_POST['mode']);
$userID = mysqli_real_escape_string($conn, $_POST['user_id']);
$emailErr = '';

if ($email == '' || strlen(str_replace(' ', '', $email)) <= 0) {
    die('empty');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // check email format
    $emailErr = 'Invalid email format. Please check your input.';
    die($emailErr);
}

if ($mode == 'register' || $mode == 'edit') { // check if account(s) with the email already exist
    $sql = "SELECT * FROM user WHERE user_email = '$email'";
    $results = mysqli_query($conn, $sql);

    if (mysqli_num_rows($results) > 0) {
        if ($mode == 'register') {
            $emailErr = 'This email address is already in use. <a href="login.php" class="err-link">Login</a> instead.';
        } else if ($mode == 'edit') {
            $row = mysqli_fetch_array($results);
            if ($row['user_id'] !== $userID) {
                $emailErr = 'This email address is already in use.';
            }
        }
    }
}

if ($emailErr == '') { // print result
    echo 'passed'; // 'passed' when valid
} else {
    echo $emailErr; // else send error message
}
