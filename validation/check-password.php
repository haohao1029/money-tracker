<?php
session_start();
if (!isset($_POST['password']) || !isset($_SESSION['user_id'])) {
    exit();
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}

include '../conn.php';

$password = mysqli_real_escape_string($conn, $_POST['password']);

if ($password == '' || strlen(str_replace(' ', '', $password)) <= 0) {
    die('empty');
}

$sql = mysqli_prepare($conn, 'SELECT user_pw FROM user WHERE user_id = ?');
mysqli_stmt_bind_param($sql, 'i', $user_id);

if (mysqli_stmt_execute($sql)) {
    $result = mysqli_stmt_get_result($sql);
    $row = mysqli_fetch_array($result);
    if (md5($password) == $row['user_pw']) { // check if the password is correct
        echo 'correct';
    } else {
        echo 'wrong';
    }
} else {
    echo 'fail';
}

mysqli_stmt_close($sql);
