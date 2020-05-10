<?php
include './function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $planID = $_POST['ebudget_plan_id'];
    $name = $_POST['ebudget_name'];
    $categ = $_POST['ebudget_category'];
    $amount = $_POST['ebudget_amount'];
    $start = $_POST['ebudget_start'];
    $wallet = $_POST['ebudget_wallet_id'];

    if (checkEmpty($name) || checkEmpty($categ) || checkEmpty($amount) || checkEmpty($start) || !is_numeric($wallet)) {
        die('empty');
    }

    if (isset($_POST['ebudget_end'])) {
        $end = $_POST['ebudget_end'];
        if (checkEmpty($end)) {
            die('Empty');
        }
    } else {
        $end = null;
    }

    if (isset($_POST['ebudget_alert'])) {
        $alert = $_POST['ebudget_alert'];
        if (checkEmpty($alert)) {
            die('Empty');
        }
    } else {
        $alert = null;
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

if ($_SESSION['access_lv'] != 1) {
    die('unauthorised');
}

include '../conn.php';

// check if plan(s) with same name, wallet, category, period exist
if ($end) {
    $sqlCheck = mysqli_prepare(
        $conn,
        "SELECT 
            plan_id 
        FROM 
            plan 
        WHERE 
            plan_name = ? 
            AND category_id = ? 
            AND wallet_id = ? 
            AND start_date = ? 
            AND end_date = ?;"
    );
    mysqli_stmt_bind_param($sqlCheck, 'siiss', $name, $categ, $wallet, $start, $end);
} else {
    $sqlCheck = mysqli_prepare(
        $conn,
        "SELECT 
            plan_id 
        FROM 
            plan 
        WHERE 
            plan_name = ?
            AND category_id = ? 
            AND wallet_id = ? 
            AND start_date = ? 
            AND end_date IS NULL;"
    );
    mysqli_stmt_bind_param($sqlCheck, 'siis', $name, $categ, $wallet, $start);
}
if (mysqli_stmt_execute($sqlCheck)) {
    $resultCheck = mysqli_stmt_get_result($sqlCheck);
    if (mysqli_num_rows($resultCheck) > 0) {
        $row = mysqli_fetch_array($resultCheck);
        if ($row['plan_id'] != $planID) {
            mysqli_stmt_close($sqlCheck);
            die('existed');
        }
    }
}

// update plan details

$sql = mysqli_prepare(
    $conn,
    "UPDATE 
        plan 
    SET 
        plan_name = ?, 
        category_id = ?, 
        plan_amount = ?, 
        plan_alert = ?, 
        start_date = ?, 
        end_date = ? 
    WHERE 
        plan_id = ?;"
);
mysqli_stmt_bind_param($sql, 'siddssi', $name, $categ, $amount, $alert, $start, $end, $planID);
if (mysqli_stmt_execute($sql) && !(mysqli_affected_rows($conn) <= 0)) {
    $status = 'success';
} else {
    $status = 'fail';
}
mysqli_stmt_close($sql);

// plan
refreshNoti($conn, $user_id);
$percent = plan($conn, $wallet, $categ)[$planID]['percentage_spent'];
$alertCount = count($_SESSION['alert']);

$joinNotiEl = getNotiEl($conn, $alertCount);

$response = array('status' => $status, 'alert_count' => $alertCount, 'new_name' => $name, 'new_percent' => $percent, 'noti_el' => $joinNotiEl);
echo json_encode($response);
