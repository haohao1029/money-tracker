<?php
function checkEmpty($var)
{
    return ((empty($var)) || ctype_space($var));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $wallet_name = htmlspecialchars($_POST['wallet_name']);
    $wallet_id = $_POST['wallet_id'];

    if (checkEmpty($wallet_id) || checkEmpty($wallet_name)) {
        die('empty');
    }
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

$sql = mysqli_prepare($conn, "UPDATE wallet SET wallet_name = ? WHERE wallet_id = ?;");
mysqli_stmt_bind_param($sql, 'si', $wallet_name, $wallet_id);
if (mysqli_stmt_execute($sql)) {
    echo $wallet_name;
} else {
    die('fail');
}
