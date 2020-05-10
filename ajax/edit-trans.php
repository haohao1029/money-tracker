<?php
include './function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $categ = $_POST['edit-category'];
    $newWallet = $_POST['edit-wallet'];
    $newAmount = $_POST['edit-amount'];
    $date = $_POST['edit-date'];
    $desc = htmlspecialchars($_POST['edit-description']);
    $transID = $_POST['trans_id'];
    $dateEdited = $_POST['date_edited'];

    $dateUnix = strtotime($date);

    if (checkEmpty($categ) || checkEmpty($newWallet) || checkEmpty($newAmount) || checkEmpty($date)) {
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

// get old wallet balance
$sqlGetBalance = mysqli_prepare($conn, "SELECT wallet_bal FROM wallet WHERE user_id = ? AND wallet_id = ?;");
mysqli_stmt_bind_param($sqlGetBalance, 'ii', $user_id, $newWallet);
if (mysqli_stmt_execute($sqlGetBalance)) {
    $resultGetBalance = mysqli_stmt_get_result($sqlGetBalance);
    $wallet_bal = mysqli_fetch_array($resultGetBalance)['wallet_bal'];
}
mysqli_stmt_close($sqlGetBalance);


// about wallet
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
        $status = 'success';
        $oldWallet = $rowTrans['wallet_id'];
        $oldTransType = $rowTrans['category_type'];
        $oldAmount = $rowTrans['trans_amount'];
        $oldCateg = $rowTrans['category_name'];
    } else {
        $status = 'fail';
    }
} else {
    $status = 'fail';
}
mysqli_stmt_close($sqlTrans);

$sqlGetType = mysqli_prepare($conn, "SELECT category_type FROM category WHERE category_id = ?;");
mysqli_stmt_bind_param($sqlGetType, 'i', $categ);
if (mysqli_stmt_execute($sqlGetType)) {
    $resultType = mysqli_stmt_get_result($sqlGetType);
    if ($rowType = mysqli_fetch_array($resultType)) {
        $newTransType = $rowType['category_type'];
    }
}
mysqli_stmt_close($sqlGetType);

$exceed = false;

// check exceed or not
if ($oldTransType == 'expenses' && $newTransType == 'expenses' && ($wallet_bal + $oldAmount < $newAmount)) {
    $exceed = true;
} else if ($oldTransType == 'income' && $newTransType == 'income' && ($wallet_bal + $newAmount < $oldAmount)) {
    $exceed = true;
} else if ($oldTransType == 'income' && $newTransType == 'expenses' && ($oldAmount + $newAmount > $wallet_bal)) {
    $exceed = true;
}

if ($exceed) {
    if ($acc_lv == 1) { // parent / individual
        die('exceed-strong');
    } else if ($acc_lv == 2) { // children
        die('exceed-weak');
    }
}

// edit
if ($dateEdited == 'true') {
    $sql = mysqli_prepare(
        $conn,
        "UPDATE 
            transaction 
        SET 
            category_id = ?, 
            wallet_id = ?, 
            trans_amount = ?, 
            trans_date = ?, 
            trans_desc = ? 
        WHERE 
            trans_id = ?;"
    );
    mysqli_stmt_bind_param($sql, 'iidssi', $categ, $newWallet, $newAmount, $date, $desc, $transID);
} else if ($dateEdited == 'false') {
    $sql = mysqli_prepare(
        $conn,
        "UPDATE 
            transaction 
        SET 
            category_id = ?, 
            wallet_id = ?, 
            trans_amount = ?, 
            trans_desc = ? 
        WHERE 
            trans_id = ?;"
    );
    mysqli_stmt_bind_param($sql, 'iidsi', $categ, $newWallet, $newAmount, $desc, $transID);
}
if (mysqli_stmt_execute($sql) && !(mysqli_affected_rows($conn) <= 0)) {
    $status = 'success';
} else {
    $status = 'fail';
}
mysqli_stmt_close($sql);

// edit wallet
if ($oldTransType == 'expenses') {
    $oldAmount *= -1;
}

if ($newTransType == 'expenses') {
    $newAmount *= -1;
}

$sqlWallet1 = mysqli_prepare($conn, "UPDATE wallet SET wallet_bal = wallet_bal - ? WHERE wallet_id = ?;");
mysqli_stmt_bind_param($sqlWallet1, 'di', $oldAmount, $oldWallet);
if (mysqli_stmt_execute($sqlWallet1) && !(mysqli_affected_rows($conn) <= 0)) {
    mysqli_stmt_close($sqlWallet1);
    $sqlWallet2 = mysqli_prepare($conn, "UPDATE wallet SET wallet_bal = wallet_bal + ? WHERE wallet_id = ?;");
    mysqli_stmt_bind_param($sqlWallet2, 'di', $newAmount, $newWallet);
    if (mysqli_stmt_execute($sqlWallet2) && !(mysqli_affected_rows($conn) <= 0)) {
        $walletUpdate = 'success';
    } else {
        $walletUpdate = 'fail';
    }
} else {
    $walletUpdate = 'fail';
}
mysqli_stmt_close($sqlWallet2);

// plan
refreshNoti($conn, $user_id);
$alertCount = count($_SESSION['alert']);

// noti item
$joinNotiEl = getNotiEl($conn, $alertCount);

// response to ajax
$response = array('status' => $status, 'date' => $dateUnix, 'alert_count' => $alertCount, 'noti_el' => $joinNotiEl);
echo json_encode($response);
