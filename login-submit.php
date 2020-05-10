<?php
session_start();
if (!isset($_POST['email'])) {
    header('Location:login.php');
    exit();
}

include './conn.php';
include './ajax/function.php';

$email = strtolower(mysqli_real_escape_string($conn, $_POST['email']));
$password = mysqli_real_escape_string($conn, $_POST['password']);
$encPw = md5($password);

// no validation here, already done with ajax

$sql = mysqli_prepare($conn, "SELECT * FROM user WHERE user_email = ? AND user_pw = ?;");
mysqli_stmt_bind_param($sql, 'ss', $email, $encPw);

if (mysqli_stmt_execute($sql)) {
    $result = mysqli_stmt_get_result($sql);

    if (mysqli_num_rows($result) <= 0) { // check for admin
        $sql = mysqli_prepare($conn, "SELECT * FROM user WHERE user_email = ? AND user_pw = ?;");
        mysqli_stmt_bind_param($sql, 'ss', $email, $password);

        if (mysqli_stmt_execute($sql)) {
            $result = mysqli_stmt_get_result($sql);
            if (mysqli_num_rows($result) <= 0) {
                echo "Account with this email address doesn't exist or password incorrect.";
                mysqli_stmt_close($sql);
                exit();
            }
        }
    }
}

if ($row = mysqli_fetch_array($result)) {
    $_SESSION['user_id'] = $row['user_id'];
    $_SESSION['user_name'] = $row['user_name'];
    $_SESSION['access_lv'] = $row['access_level'];
    $_SESSION['alert'] = array();
    // family
    if ($row['family_id'] != null) {
        $_SESSION['family_id'] = $row['family_id'];
    }
}
mysqli_stmt_close($sql);

if ($_SESSION['access_lv'] == 0) {
    echo 'admin';
} else if ($_SESSION['access_lv'] > 0) {
    echo 'user';
}
