<?php
if (isset($_POST['wallet_id'])) {
    $wallet_id = $_POST['wallet_id'];
} else {
    header('Location:wallet.php');
    exit();
}

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location:login.php');
    exit();
} else {
    $user_id = $_SESSION['user_id'];
}

include '../conn.php';

$sql = mysqli_prepare($conn, "SELECT wallet_name FROM wallet WHERE wallet_status = 1 and wallet_id = ?");
mysqli_stmt_bind_param($sql, 'i', $wallet_id);
if (mysqli_stmt_execute($sql)) {
    $result = mysqli_stmt_get_result($sql);
    if ($row = mysqli_fetch_array($result)) {
        $wallet_name = $row['wallet_name'];
    } else {
        die('fail');
    }
} else {
    die('fail');
}

echo $wallet_name;
