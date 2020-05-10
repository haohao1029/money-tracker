<?php
include "conn.php";
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location:login.php');
    exit();
} else {
    $family_id = isset($_SESSION['family_id']) ? $_SESSION['family_id'] : null;
    $acc_lv = $_SESSION['access_lv'];
    $user_id = $_SESSION['user_id'];
}
$family_name = $_POST['family_name'];


$sql = mysqli_prepare($conn, "INSERT INTO family (family_name) VALUES (?);");
mysqli_stmt_bind_param($sql, 's', $family_name);

mysqli_stmt_execute($sql);

$family_id = mysqli_insert_id($conn); 

$sql = mysqli_prepare($conn, "UPDATE user SET family_id = ? WHERE user_id = $user_id");
mysqli_stmt_bind_param($sql, 's', $family_id);
mysqli_stmt_execute($sql);

echo '<script>alert("Family Created!");
    window.location.href = "logout.php" ; 
    </script>';


mysqli_stmt_close($sql);
