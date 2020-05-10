<?php
if (!isset($_POST['plan_id'])) {
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
include '../ajax/function.php';

$plan_id = $_POST['plan_id'];
$sql = mysqli_prepare(
    $conn,
    "SELECT
        p.plan_name,
        p.category_id,
        c.category_name,
        p.wallet_id,
        w.wallet_name,
        p.plan_amount,
        p.plan_alert,
        p.start_date,
        p.end_date
    FROM
        plan p
        INNER JOIN category c ON p.category_id = c.category_id
        INNER JOIN wallet w ON p.wallet_id = w.wallet_id
    WHERE
        p.plan_id = ?;"
);
mysqli_stmt_bind_param($sql, 'i', $plan_id);
if (mysqli_stmt_execute($sql)) {
    $result = mysqli_stmt_get_result($sql);
    if (!(mysqli_num_rows($result) <= 0)) {
        if ($row = mysqli_fetch_array($result)) {
            $plan_name  = $row['plan_name'];
            $cat_id     = $row['category_id'];
            $cat_name   = $row['category_name'];
            $wall_id    = $row['wallet_id'];
            $wall_name  = $row['wallet_name'];
            $amount     = $row['plan_amount'];
            $alert      = $row['plan_alert'];
            $start      = date('d-m-Y, l', strtotime($row['start_date']));
            $end = $row['end_date'] ? date('d-m-Y, l', strtotime($row['end_date'])) : null;
        }
    }
}
mysqli_stmt_close($sql);

$spent = getAmountSpent($conn, $cat_id, $wall_id, $row['start_date'], $row['end_date']);
$percent = calcPercentSpent($spent, $amount);

$response = array(
    'plan_name' => $plan_name,
    'cat_id'    => $cat_id,
    'cat_name'  => $cat_name,
    'wall_id'   => $wall_id,
    'wall_name' => $wall_name,
    'amount'    => $amount,
    'alert'     => $alert,
    'spent'     => $spent,
    'percent'   => $percent,
    'start'     => $start,
    'end'       => $end
);

echo json_encode($response);
