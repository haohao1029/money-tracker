<?php
if (!isset($_POST['action_val'])) {
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
$action = $_POST['action_val'];
$dateShowing = $_POST['date_showing'];

if ($action == 'showing') {
    $targetDate = $dateShowing;
} else if ($action == 0) { // go to current month
    $targetDate = time();
} else if ($action == 1) { // next month
    // $targetDate = strtotime($action . ' month', $dateShowing);
    $targetDate = strtotime('last day of next month', $dateShowing);
} else if ($action == -1) { // prev month
    $targetDate = strtotime('first day of last month', $dateShowing);
} else {
    // error
}

$year = date('Y', $targetDate);
$month = date('n', $targetDate);

// expenses
$sqlExpenses = mysqli_prepare(
    $conn,
    "SELECT 
        SUM(t.trans_amount) AS 'sum_expenses' 
    FROM 
        transaction t 
        INNER JOIN category c ON t.category_id = c.category_id 
        INNER JOIN wallet w ON t.wallet_id = w.wallet_id 
        INNER JOIN user u ON w.user_id = u.user_id 
    WHERE 
        u.user_id = ? 
        AND c.category_type = 'expenses' 
        AND YEAR(t.trans_date) = ? 
        AND MONTH(t.trans_date) = ? 
        AND w.wallet_status = 1;"
);
mysqli_stmt_bind_param($sqlExpenses, 'iii', $user_id, $year, $month);
if (mysqli_stmt_execute($sqlExpenses)) {
    $resultExpenses = mysqli_stmt_get_result($sqlExpenses);

    if (mysqli_num_rows($resultExpenses) <= 0) {
        $expenses = 0;
    } else {
        if ($row = mysqli_fetch_array($resultExpenses)) {
            $expenses = $row['sum_expenses'];
            if ($row['sum_expenses'] == NULL) {
                $expenses = 0;
            }
        }
    }
}
mysqli_stmt_close($sqlExpenses);

// income
$sqlIncome = mysqli_prepare(
    $conn,
    "SELECT 
        SUM(t.trans_amount) AS 'sum_income' 
    FROM 
        transaction t 
        INNER JOIN category c ON t.category_id = c.category_id 
        INNER JOIN wallet w ON t.wallet_id = w.wallet_id 
        INNER JOIN user u ON w.user_id = u.user_id 
    WHERE 
        u.user_id = ? 
        AND c.category_type = 'income' 
        AND YEAR(t.trans_date) = ? 
        AND MONTH(t.trans_date) = ? 
        AND w.wallet_status = 1;"
);
mysqli_stmt_bind_param($sqlIncome, 'iii', $user_id, $year, $month);
if (mysqli_stmt_execute($sqlIncome)) {
    $resultIncome = mysqli_stmt_get_result($sqlIncome);

    if (mysqli_num_rows($resultIncome) <= 0) {
        $income = 0;
    } else {
        if ($row = mysqli_fetch_array($resultIncome)) {
            $income = $row['sum_income'];
            if ($row['sum_income'] == NULL) {
                $income = 0;
            }
        }
    }
}
mysqli_stmt_close($sqlIncome);

$date = date('F Y', $targetDate);
$total = number_format($income - $expenses, 2, '.', '');
$expenses = number_format($expenses, 2, '.', '');
$income = number_format($income, 2, '.', '');

$response = array('date' => $date, 'unixdate' => $targetDate, 'expenses' => $expenses, 'income' => $income, 'total' => $total, 'year' => $year, 'month' => $month);

echo json_encode($response);
