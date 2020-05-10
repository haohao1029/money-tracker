<?php
if (!isset($_POST['plan_id'])) {
    header('Location:wallet.php');
    exit();
} else {
    $planID = $_POST['plan_id'];
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

$sql = mysqli_prepare($conn, "DELETE FROM plan WHERE plan_id = ?;");
mysqli_stmt_bind_param($sql, 'i', $planID);
if (mysqli_stmt_execute($sql) && !(mysqli_affected_rows($conn) <= 0)) {
    $status = 'success';
} else {
    $status = 'fail';
}
mysqli_stmt_close($sql);

// plan
removeAlert($planID);
refreshNoti($conn, $user_id);
$alertCount = count($_SESSION['alert']);

$joinNotiEl = getNotiEl($conn, $alertCount);

$response = array('status' => $status, 'alert_count' => $alertCount, 'noti_el' => $joinNotiEl);
echo json_encode($response);
