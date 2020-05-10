<?php
include './function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $categ = $_POST['category'];
    $wallet = $_POST['wallet'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $desc = htmlspecialchars($_POST['description']);

    $dateUnix = strtotime($date);

    if (checkEmpty($categ) || checkEmpty($wallet) || checkEmpty($amount) || checkEmpty($date) || !is_numeric($amount)) {
        die('empty');
    }
} else {
    header('Location:transaction.php');
    exit();
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

$sqlGetType = mysqli_prepare($conn, "SELECT category_type FROM category WHERE category_id = ?;");
mysqli_stmt_bind_param($sqlGetType, 'i', $categ);
if (mysqli_stmt_execute($sqlGetType)) {
    $resultType = mysqli_stmt_get_result($sqlGetType);
    if ($rowType = mysqli_fetch_array($resultType)) {
        $transType = $rowType['category_type'];
    }
}
mysqli_stmt_close($sqlGetType);

// check whether amount entered exceed wallet balance
if ($transType == 'expenses') {
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
}

// insert
$today = date('Y-m-d');

if ($date == 'today' || $date == $today) {
    $sql = mysqli_prepare($conn, "INSERT INTO transaction (category_id, wallet_id, trans_amount, trans_desc) 
    VALUES (?, ?, ? ,?);");
    mysqli_stmt_bind_param($sql, 'iids', $categ, $wallet, $amount, $desc);
} else {
    $sql = mysqli_prepare(
        $conn,
        "INSERT INTO transaction (category_id, wallet_id, trans_amount, trans_date, trans_desc) 
    VALUES (?, ?, ?, ? ,?);"
    );
    mysqli_stmt_bind_param($sql, 'iidss', $categ, $wallet, $amount, $date, $desc);
}

if (mysqli_stmt_execute($sql) && !(mysqli_affected_rows($conn) <= 0)) {
    $status = 'success';
} else {
    $status = 'fail';
}
mysqli_stmt_close($sql);

// update wallet
if ($transType == 'expenses') {
    $amount *= -1;
}

$sqlWallet = mysqli_prepare($conn, "UPDATE wallet SET wallet_bal = wallet_bal + ? WHERE wallet_id = ?;");
mysqli_stmt_bind_param($sqlWallet, 'di', $amount, $wallet);
if (mysqli_stmt_execute($sqlWallet) && !(mysqli_affected_rows($conn) <= 0)) {
    $walletUpdate = 'success';
} else {
    $walletUpdate = 'fail';
}
mysqli_stmt_close($sqlWallet);

// plan
refreshNoti($conn, $user_id);
$alertCount = count($_SESSION['alert']);

// noti item
$joinNotiEl = getNotiEl($conn, $alertCount);

// response to ajax
$response = array('status' => $status, 'date' => $dateUnix, 'alert_count' => $alertCount, 'noti_el' => $joinNotiEl);
echo json_encode($response);
