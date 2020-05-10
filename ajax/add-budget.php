<?php
include './function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['budget_name'];
    $categ = $_POST['category'];
    $amount = $_POST['amount'];
    $start_date = $_POST['start_date'];
    $wallet = $_POST['wallet_id'];

    if (checkEmpty($name) || checkEmpty($categ) || checkEmpty($amount) || checkEmpty($start_date) || !is_numeric($wallet)) {
        die('empty');
    }

    if (isset($_POST['end_date'])) {
        $end_date = $_POST['end_date'];
        if (checkEmpty($end_date)) {
            die('Empty');
        }
    } else {
        $end_date = null;
    }

    if (isset($_POST['alert'])) {
        $alert = $_POST['alert'];
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
if ($end_date) {
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
    mysqli_stmt_bind_param($sqlCheck, 'siiss', $name, $categ, $wallet, $start_date, $end_date);
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
    mysqli_stmt_bind_param($sqlCheck, 'siis', $name, $categ, $wallet, $start_date);
}
if (mysqli_stmt_execute($sqlCheck)) {
    $resultCheck = mysqli_stmt_get_result($sqlCheck);
    if (mysqli_num_rows($resultCheck) > 0) {
        mysqli_stmt_close($sqlCheck);
        die('existed');
    }
}

// insert
$sql = mysqli_prepare(
    $conn,
    "INSERT INTO 
        plan 
        (plan_name, category_id, wallet_id, plan_amount, plan_alert, start_date, end_date, creator_user_id) 
    VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?);"
);
mysqli_stmt_bind_param($sql, 'siiddssi', $name, $categ, $wallet, $amount, $alert, $start_date, $end_date, $user_id);
if (mysqli_stmt_execute($sql) && !(mysqli_affected_rows($conn) <= 0)) {
    // success
    $status = 'success';
    $new_plan_id = mysqli_insert_id($conn);
} else {
    $status = 'fail';
}
mysqli_stmt_close($sql);

// plan
refreshNoti($conn, $user_id);
$percent = plan($conn, $wallet, $categ)[$new_plan_id]['percentage_spent'];
$alertCount = count($_SESSION['alert']);

$joinNotiEl = getNotiEl($conn, $alertCount);

// plan-item html element
$element =
    "<div class=\"plan-item btn-eff row\" data-value=\"$new_plan_id\">
        <div class=\"plan-name col-7-5 col-sm-9 col-md-9 col-lg-9-5 col-xl-10\">
            $name
        </div>
        <div class=\"spent row col-4-5 col-sm-3 col-md-3 col-lg-2-5 col-xl-2\">
            <div class=\"col-5\">Spent:</div>
            <span class=\"col-7\">$percent %</span>
        </div>
        <div class=\"clicked\"></div>
    </div>";

$response = array('status' => $status, 'alert_count' => $alertCount, 'plan_item' => $element, 'noti_el' => $joinNotiEl);
echo json_encode($response);
