<?php
if (!isset($_POST['trans_id'])) {
    header('Location:transaction.php');
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

$trans_id = $_POST['trans_id'];

$sql = mysqli_prepare(
    $conn,
    "SELECT 
        c.category_id, 
        c.category_name, 
        c.category_type, 
        t.trans_amount, 
        t.trans_date, 
        t.trans_desc, 
        w.wallet_id, 
        w.wallet_name 
    FROM 
        transaction t 
        INNER JOIN category c ON t.category_id = c.category_id 
        INNER JOIN wallet w ON t.wallet_id = w.wallet_id 
        INNER JOIN user u ON w.user_id = u.user_id 
    WHERE 
        u.user_id = ? 
        AND t.trans_id = ?;"
);
mysqli_stmt_bind_param($sql, 'ii', $user_id, $trans_id);
if (mysqli_stmt_execute($sql)) {
    $result = mysqli_stmt_get_result($sql);
    if (!(mysqli_num_rows($result) <= 0)) {
        if ($row = mysqli_fetch_array($result)) {
            $cat_id     = $row['category_id'];
            $cat_name   = $row['category_name'];
            $date       = date('d-m-Y, l', strtotime($row['trans_date']));
            $type       = $row['category_type'];
            $wallet_id  = $row['wallet_id'];
            $wallet     = $row['wallet_name'];
            $amount     = $row['trans_amount'];
            $desc       = $row['trans_desc'];
        }
    }
}
mysqli_stmt_close($sql);

$response = array(
    'cat_id'    => $cat_id,
    'category'  => $cat_name,
    'date'      => $date,
    'type'      => $type,
    'wallet_id' => $wallet_id,
    'wallet'    => $wallet,
    'amount'    => $amount,
    'desc'      => $desc
);

echo json_encode($response);
