<?php
include './function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['transfer_mode'])) {
        $mode = 0; // 0: child -> parent (deduct from child)
    } else {
        $mode = 1; // 1: parent -> child (top up for child)
    }

    $wallet_parent = $_POST['wallet_parent'];
    $wallet_child = $_POST['wallet_child'];
    $amount = $_POST['transfer_amount'];

    if (checkEmpty($wallet_parent) || checkEmpty($wallet_child) || checkEmpty($amount)) {
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

if ($mode == 0) { // - child + parent
    $walletOut = $wallet_child;
    $walletIn = $wallet_parent;
} else if ($mode == 1) { // + child - parent
    $walletOut = $wallet_parent;
    $walletIn = $wallet_child;
}

// get the original amount of the wallet to be deducted
$sqlOldAmount = mysqli_prepare($conn, "SELECT wallet_bal FROM wallet WHERE wallet_id = ?;");
mysqli_stmt_bind_param($sqlOldAmount, 'i', $walletOut);
if (mysqli_stmt_execute($sqlOldAmount)) {
    $resultOldAmount = mysqli_stmt_get_result($sqlOldAmount);
    $oldAmount = mysqli_fetch_array($resultOldAmount)['wallet_bal'];
    mysqli_stmt_close($sqlOldAmount);
    if ($oldAmount < $amount) {
        die('insufficient');
    }
}

// transfer
$sqlIn = mysqli_prepare($conn, "UPDATE wallet SET wallet_bal = wallet_bal + ? WHERE wallet_id = ?;");
mysqli_stmt_bind_param($sqlIn, 'di', $amount, $walletIn);
if (mysqli_stmt_execute($sqlIn) && !(mysqli_affected_rows($conn) <= 0)) {
    mysqli_stmt_close($sqlIn);

    $sqlOut = mysqli_prepare($conn, "UPDATE wallet SET wallet_bal = wallet_bal - ? WHERE wallet_id = ?;");
    mysqli_stmt_bind_param($sqlOut, 'di', $amount, $walletOut);
    if (mysqli_stmt_execute($sqlOut) && !(mysqli_affected_rows($conn) <= 0)) {
        $status = 'success';
    } else {
        $status = 'fail';
    }
} else {
    $status = 'fail';
}
mysqli_stmt_close($sqlOut);

// get the balance of both wallet so live update can be conducted
$sqlParent = mysqli_prepare($conn, "SELECT wallet_bal FROM wallet WHERE wallet_id = ?;");
mysqli_stmt_bind_param($sqlParent, 'i', $wallet_parent);
if (mysqli_stmt_execute($sqlParent)) {
    $resultParent = mysqli_stmt_get_result($sqlParent);
    $parentWallet = mysqli_fetch_array($resultParent)['wallet_bal'];
}
mysqli_stmt_close($sqlParent);

$sqlChild = mysqli_prepare($conn, "SELECT wallet_bal FROM wallet WHERE wallet_id = ?;");
mysqli_stmt_bind_param($sqlChild, 'i', $wallet_child);
if (mysqli_stmt_execute($sqlChild)) {
    $resultChild = mysqli_stmt_get_result($sqlChild);
    $childWallet = mysqli_fetch_array($resultChild)['wallet_bal'];
}
mysqli_stmt_close($sqlChild);


$response = array('status' => $status, 'child_id' => $wallet_child, 'child_wallet' => $childWallet, 'parent_id' => $wallet_parent, 'parent_wallet' => $parentWallet);
echo json_encode($response);
