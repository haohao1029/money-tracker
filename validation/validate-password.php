<?php
if (!isset($_POST['password'])) {
    exit();
}

include '../conn.php';

$password = mysqli_real_escape_string($conn, $_POST['password']);
$cfmpassword = mysqli_real_escape_string($conn, $_POST['cfm_password']);

if ($password == '' || $cfmpassword == '' || strlen(str_replace(' ', '', $password)) <= 0 || strlen(str_replace(' ', '', $cfmpassword)) <= 0) {
    die('empty');
}

if ($password !== $cfmpassword) {
    echo 'Confirmation password does not matches!';
} else {
    echo 'passed';
}
