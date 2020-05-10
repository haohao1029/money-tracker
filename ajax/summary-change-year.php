<?php
if (!isset($_POST['action_val'])) {
    header('Location:summary.php');
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

if ($action == 0) { // go to current month
    $targetDate = time();
} else if ($action == 1 || $action == -1) { // next / prev month
    $targetDate = strtotime($action . 'year', $dateShowing);
} else {
    // error
}

$year = date('Y', $targetDate);
$month = date('n', $targetDate);

//IncomeBar
$incomesBar = "";
$expensesBar = "";

$sqlIncomeBar = mysqli_prepare(
    $conn,
    "SELECT
        SUM(trans_amount) AS income
    FROM
        transaction t 
        INNER JOIN category c ON t.category_id = c.category_id 
        INNER JOIN wallet w ON t.wallet_id = w.wallet_id 
        INNER JOIN user u ON w.user_id = u.user_id 
    WHERE 
        category_type = 'income' 
        AND u.user_id = ?
        AND YEAR(t.trans_date) = ?"
);
mysqli_stmt_bind_param($sqlIncomeBar, 'ii', $user_id, $year);
if (mysqli_stmt_execute($sqlIncomeBar)) {
    $resultIncomeBar = mysqli_stmt_get_result($sqlIncomeBar);
    while ($row = mysqli_fetch_array($resultIncomeBar)) {
        $incomeBar = $row['income'];
    }
}
mysqli_stmt_close($sqlIncomeBar);
//Expense Bar
$sqlExpenseBar = mysqli_prepare(
    $conn,
    "SELECT
        SUM(trans_amount) AS expense
    FROM
        transaction t 
        INNER JOIN category c ON t.category_id = c.category_id 
        INNER JOIN wallet w ON t.wallet_id = w.wallet_id 
        INNER JOIN user u ON w.user_id = u.user_id 
    WHERE 
        category_type = 'expenses' 
        AND u.user_id = ?
        AND YEAR(t.trans_date) = ?"
);
mysqli_stmt_bind_param($sqlExpenseBar, 'ii', $user_id, $year);
if (mysqli_stmt_execute($sqlExpenseBar)) {
    $resultExpenseBar = mysqli_stmt_get_result($sqlExpenseBar);
    while ($row = mysqli_fetch_array($resultExpenseBar)) {
        $expenseBar = $row['expense'];
    }
}
mysqli_stmt_close($sqlExpenseBar);

//income pie
$incomes = '';
$pcategories = '';
$sqlIncomePie = mysqli_prepare(
    $conn,
    "SELECT 
        SUM(trans_amount) AS incomes,
        category_name 
    FROM 
    transaction t 
        INNER JOIN category c ON t.category_id = c.category_id 
        INNER JOIN wallet w ON t.wallet_id = w.wallet_id 
        INNER JOIN user u ON w.user_id = u.user_id 
    WHERE 
        category_type = 'income' 
        AND u.user_id = ?
        AND YEAR(t.trans_date) = ? 
    GROUP BY 
        category_name"
);
mysqli_stmt_bind_param($sqlIncomePie, 'ii', $user_id, $year);
if (mysqli_stmt_execute($sqlIncomePie)) {
    $resultIncomePie = mysqli_stmt_get_result($sqlIncomePie);

    while ($row = mysqli_fetch_array($resultIncomePie)) {
        $income = $row['incomes'];
        $pcategory = $row['category_name'];

        $incomes = $incomes . '' . $income . ',';
        $pcategories = $pcategories . '' . $pcategory . ',';
    }
    $incomes = trim($incomes, ",");
    $pcategories = trim($pcategories, ",");
}


//expense pie
$expenses = '';
$ecategories = '';
$sqlExpensePie = mysqli_prepare(
    $conn,
    "SELECT 
        SUM(trans_amount) AS expenses,
        category_name 
    FROM 
        transaction t 
        INNER JOIN category c ON t.category_id = c.category_id 
        INNER JOIN wallet w ON t.wallet_id = w.wallet_id 
        INNER JOIN user u ON w.user_id = u.user_id 
    WHERE 
        category_type = 'expenses'
        AND u.user_id = ?
        AND YEAR(t.trans_date) = ? 
    GROUP BY 
        category_name"
);
mysqli_stmt_bind_param($sqlExpensePie, 'ii', $user_id, $year);
if (mysqli_stmt_execute($sqlExpensePie)) {
    $resultExpensePie = mysqli_stmt_get_result($sqlExpensePie);
    while ($row = mysqli_fetch_array($resultExpensePie)) {
        $expense = $row['expenses'];
        $ecategory = $row['category_name'];

        $expenses = $expenses . '' . $expense . ',';
        $ecategories = $ecategories . '' . $ecategory . ',';
    }
    $expenses = trim($expenses, ",");
    $ecategories = trim($ecategories, ",");
}
mysqli_stmt_close($sqlExpensePie);

$date = date('Y', $targetDate);

$response = array(
    'date' => $date, 'unixdate' => $targetDate,  'incomeBar' => $incomeBar, 'expenseBar' => $expenseBar, 'pieIncome' => $incomes,
    'pieIncomeCategory' => $pcategories, 'pieExpense' => $expenses, 'pieExpenseCategory' => $ecategories
);
echo json_encode($response);
