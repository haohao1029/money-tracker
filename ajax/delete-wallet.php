<?php
if (!isset($_POST['wallet_id'])) {
    header('Location:wallet.php');
    exit();
} else {
    $walletID = $_POST['wallet_id'];
}

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location:login.php');
    exit();
} else {
    $user_id = $_SESSION['user_id'];
}

include '../conn.php';
include '../ajax/function.php';

// change status to 0 (simulate delete)
$sql = mysqli_prepare($conn, "UPDATE wallet SET wallet_status = 0 WHERE wallet_id = ?;");
mysqli_stmt_bind_param($sql, 'i', $walletID);
if (mysqli_stmt_execute($sql) && !(mysqli_affected_rows($conn) <= 0)) {
    $status = 'success';
} else {
    $status = 'fail';
}
mysqli_stmt_close($sql);

// plan
$sqlPlan = mysqli_prepare($conn, "SELECT plan_id FROM plan WHERE wallet_id = ?;");
mysqli_stmt_bind_param($sqlPlan, 'i', $walletID);
if (mysqli_stmt_execute($sqlPlan)) {
    $resultPlan = mysqli_stmt_get_result($sqlPlan);
    if (mysqli_num_rows($resultPlan) > 0) {
        $arrayOfArrayPlan = array();
        while ($rowPlan = mysqli_fetch_array($resultPlan)) {
            removeAlert($rowPlan['plan_id']);
        }
    }
}
mysqli_stmt_close($sqlPlan);

refreshNoti($conn, $user_id);
$alertCount = count($_SESSION['alert']);
$joinNotiEl = getNotiEl($conn, $alertCount);

$response = array('status' => $status, 'alert_count' => $alertCount, 'noti_el' => $joinNotiEl);
echo json_encode($response);
