<?php
if (!isset($_POST['trans_id'])) {
    header('Location:transaction.php');
    exit();
} else {
    $transID = $_POST['trans_id'];
}

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location:login.php');
    exit();
} else {
    $user_id = $_SESSION['user_id'];
    $acc_lv = $_SESSION['access_lv'];
}

include '../conn.php';
include './function.php';

$sqlTrans = mysqli_prepare(
    $conn,
    "SELECT 
        wallet_id, 
        trans_amount, 
        category_type, 
        category_name
    FROM 
        transaction t
        INNER JOIN category c ON t.category_id = c.category_id 
    WHERE 
        trans_id = ?;"
);
mysqli_stmt_bind_param($sqlTrans, 'i', $transID);
if (mysqli_stmt_execute($sqlTrans)) {
    $resultTrans = mysqli_stmt_get_result($sqlTrans);
    if ($rowTrans = mysqli_fetch_array($resultTrans)) {
        $wallet = $rowTrans['wallet_id'];
        $transType = $rowTrans['category_type'];
        $amount = $rowTrans['trans_amount'];
        $categ = $rowTrans['category_name'];
    } else {
        $status = 'fail';
    }
} else {
    $status = 'fail';
}
mysqli_stmt_close($sqlTrans);

if ($transType == 'income') {
    // get wallet balance
    $sqlGetBalance = mysqli_prepare($conn, "SELECT wallet_bal FROM wallet WHERE user_id = ? AND wallet_id = ?;");
    mysqli_stmt_bind_param($sqlGetBalance, 'ii', $user_id, $wallet);
    if (mysqli_stmt_execute($sqlGetBalance)) {
        $resultGetBalance = mysqli_stmt_get_result($sqlGetBalance);
        $wallet_bal = mysqli_fetch_array($resultGetBalance)['wallet_bal'];
    }
    mysqli_stmt_close($sqlGetBalance);
    // check exceed or not
    if ($amount > $wallet_bal) {
        if ($acc_lv == 1) { // parent / individual
            die('exceed-strong');
        } else if ($acc_lv == 2) { // children
            die('exceed-weak');
        }
    }
} else {
    $amount *= -1;
}

$sqlWallet = mysqli_prepare($conn, "UPDATE wallet SET wallet_bal = wallet_bal - ? WHERE wallet_id = ?;");
mysqli_stmt_bind_param($sqlWallet, 'di', $amount, $wallet);
if (mysqli_stmt_execute($sqlWallet) && !(mysqli_affected_rows($conn) <= 0)) {
    $walletUpdate = 'success';
} else {
    $walletUpdate = 'fail';
}
mysqli_stmt_close($sqlWallet);

$sql = mysqli_prepare($conn, "DELETE FROM transaction WHERE trans_id = ?;");
mysqli_stmt_bind_param($sql, 'i', $transID);
if (mysqli_stmt_execute($sql) && !(mysqli_affected_rows($conn) <= 0)) {
    $status = 'success';
} else {
    $status = 'fail';
}
mysqli_stmt_close($sql);

// plan
refreshNoti($conn, $user_id);
$alertCount = count($_SESSION['alert']);

// noti item
$joinNotiEl = getNotiEl($conn, $alertCount);

$response = array('status' => $status, 'alert_count' => $alertCount, 'noti_el' => $joinNotiEl);
echo json_encode($response);
