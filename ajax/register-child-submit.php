<?php
if (!isset($_POST['email'])) { // prevent ones from entering here directly by url
    exit();
}
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location:login.php');
    exit();
} else {
    $family_id = isset($_SESSION['family_id']) ? $_SESSION['family_id'] : null;
}

include '../conn.php';

$username = mysqli_real_escape_string($conn, $_POST['username']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = mysqli_real_escape_string($conn, $_POST['password']);
$encPw = md5($password);
$access = "2";
// no validation here, all validation is done before submitting

// sql to insert new user account record
$sql = mysqli_prepare($conn, "INSERT INTO user (user_name, user_email, user_pw, family_id, access_level) 
VALUES (?, ?, ?,?,?);");
mysqli_stmt_bind_param($sql, 'sssss', $username, $email, $encPw, $family_id, $access);
if (mysqli_stmt_execute($sql)) {

echo '<script>alert("Child Account Added!");
window.location.href = "../family.php" ; 
</script>';
}

mysqli_stmt_close($sql);
