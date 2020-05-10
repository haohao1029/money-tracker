<?php
session_start();
include("../conn.php");
if (isset($_POST['Submit'])) {
    $username = $_SESSION['user_name'];
    $oldpass = md5($_POST['current_password']);
    $newpassword = md5($_POST['new_password']);
    $confirm_new_password = md5($_POST['confirm_password']);
    if($confirm_new_password != $newpassword){
        echo "<script>alert('Password Not Match!');</script>";
        echo "<script>window.location.href='../account.php';</script>";
    }  else {
        $sql = mysqli_query($conn, "SELECT user_pw FROM user WHERE user_name='$username'");
        $num = mysqli_fetch_array($sql);
        if ($num > 0) {
            $con = mysqli_query($conn, "UPDATE user SET user_pw='$newpassword' WHERE user_name ='$username'");
            echo "<script>alert('Password Updated!');</script>";
            echo "<script>window.location.href='../logout.php';</script>";
        }    
    }   
}
