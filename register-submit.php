<?php
if (!isset($_POST['email'])) { // prevent ones from entering here directly by url
    exit();
}
include './conn.php';

$username = mysqli_real_escape_string($conn, $_POST['username']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = mysqli_real_escape_string($conn, $_POST['password']);
$encPw = md5($password);

// no validation here, all validation is done before submitting

// sql to insert new user account record
$sql = mysqli_prepare($conn, "INSERT INTO user (user_name, user_email, user_pw) 
VALUES (?, ?, ?);");
mysqli_stmt_bind_param($sql, 'sss', $username, $email, $encPw);

if (mysqli_stmt_execute($sql)) {
    if (mysqli_affected_rows($conn) <= 0) {
        echo 'fail';
    } else {
        echo 'success';
    }
} else {
    echo 'fail';
}

mysqli_stmt_close($sql);
