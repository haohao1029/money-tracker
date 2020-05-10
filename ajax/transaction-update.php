<?php
if (!isset($_POST['target_unix'])) {
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
$unix = $_POST['target_unix'];
$year = date('Y', $unix);
$month = date('n', $unix);

$sqlDay = mysqli_prepare(
    $conn,
    "SELECT 
        DATE(t.trans_date) AS 'date' 
    FROM 
        transaction t 
        INNER JOIN category c ON t.category_id = c.category_id 
        INNER JOIN wallet w ON t.wallet_id = w.wallet_id 
        INNER JOIN user u ON w.user_id = u.user_id 
    WHERE 
        u.user_id = ? 
        AND YEAR(t.trans_date) = ? 
        AND MONTH(t.trans_date) = ? 
        AND w.wallet_status = 1 
    GROUP BY 
        date 
    ORDER BY 
        date DESC;"
);
mysqli_stmt_bind_param($sqlDay, 'iii', $user_id, $year, $month);
if (mysqli_stmt_execute($sqlDay)) {
    $result = mysqli_stmt_get_result($sqlDay);
    if (!(mysqli_num_rows($result) <= 0)) {
        while ($rowDay = mysqli_fetch_array($result)) {
?>
            <div class="row center">
                <div class="trans-list col-11 col-sm-10 col-md-9 col-lg-8 col-xl-7 animated bounceInUp delay-0-2s">
                    <div class="date-head">
                        <div class="date"><?php echo date('d M', strtotime($rowDay['date'])); ?></div>
                        <div class="daily-total">
                            <?php
                            $relYear = date('Y', strtotime($rowDay['date']));
                            $relMonth = date('n', strtotime($rowDay['date']));
                            $relDay = date('j', strtotime($rowDay['date']));

                            // daily expenses
                            $sqlExpenses = mysqli_prepare(
                                $conn,
                                "SELECT 
                                SUM(t.trans_amount) AS 'expenses' 
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
                                AND DAY(t.trans_date) = ? 
                                AND w.wallet_status = 1;"
                            );
                            mysqli_stmt_bind_param($sqlExpenses, 'iiii', $user_id, $relYear, $relMonth, $relDay);
                            if (mysqli_stmt_execute($sqlExpenses)) {
                                $resultExpenses = mysqli_stmt_get_result($sqlExpenses);

                                if (mysqli_num_rows($resultExpenses) <= 0) {
                                    $expenses = 0;
                                } else {
                                    if ($rowExpenses = mysqli_fetch_array($resultExpenses)) {
                                        $expenses = $rowExpenses['expenses'];
                                        if ($rowExpenses['expenses'] == NULL) {
                                            $expenses = 0;
                                        }
                                    }
                                }
                            }
                            mysqli_stmt_close($sqlExpenses);

                            // daily income
                            $sqlIncome = mysqli_prepare(
                                $conn,
                                "SELECT 
                                SUM(t.trans_amount) AS 'income' 
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
                                AND DAY(t.trans_date) = ? 
                                AND w.wallet_status = 1;"
                            );
                            mysqli_stmt_bind_param($sqlIncome, 'iiii', $user_id, $relYear, $relMonth, $relDay);
                            if (mysqli_stmt_execute($sqlIncome)) {
                                $resultIncome = mysqli_stmt_get_result($sqlIncome);

                                if (mysqli_num_rows($resultIncome) <= 0) {
                                    $income = 0;
                                } else {
                                    if ($rowIncome = mysqli_fetch_array($resultIncome)) {
                                        $income = $rowIncome['income'];
                                        if ($rowIncome['income'] == NULL) {
                                            $income = 0;
                                        }
                                    }
                                }
                            }
                            mysqli_stmt_close($sqlIncome);


                            ?>
                            <div class="daily-expenses">
                                <span>Expenses:</span>
                                <?php echo number_format($expenses, 2, '.', ''); ?>
                            </div>
                            <div class="daily-income">
                                <span>Income:</span>
                                <?php echo number_format($income, 2, '.', ''); ?>
                            </div>
                            <div class="daily-sum">
                                <span>Total:</span>
                                <?php echo number_format($income - $expenses, 2, '.', ''); ?>
                            </div>
                        </div>
                    </div>
                    <div class="date-body">
                        <?php
                        $sqlDetail = mysqli_prepare(
                            $conn,
                            "SELECT
                            c.category_type,
                            c.category_name,
                            t.trans_desc,
                            t.trans_amount,
                            t.trans_id
                        FROM
                            transaction t 
                            INNER JOIN category c ON t.category_id = c.category_id 
                            INNER JOIN wallet w ON t.wallet_id = w.wallet_id 
                            INNER JOIN user u ON w.user_id = u.user_id 
                        WHERE
                            u.user_id = ? 
                            AND YEAR(t.trans_date) = ? 
                            AND MONTH(t.trans_date) = ? 
                            AND DAY(t.trans_date) = ? 
                            AND w.wallet_status = 1 
                        ORDER BY
                            t.trans_date DESC;"
                        );
                        mysqli_stmt_bind_param($sqlDetail, 'iiii', $user_id, $relYear, $relMonth, $relDay);
                        if (mysqli_stmt_execute($sqlDetail)) {
                            $resultDetail = mysqli_stmt_get_result($sqlDetail);

                            if (!(mysqli_num_rows($resultDetail) <= 0)) {
                                while ($rowDetail = mysqli_fetch_array($resultDetail)) {
                        ?>
                                    <div class="row trans-item trans-<?php echo $rowDetail['category_type']; ?>" data-value="<?php echo $rowDetail['trans_id']; ?>">
                                        <div class="trans-detail col-10">
                                            <div class="trans-category"><?php echo $rowDetail['category_name']; ?></div>
                                            <div class="trans-desc"><?php echo $rowDetail['trans_desc']; ?></div>
                                        </div>
                                        <div class="trans-amount col-2"><?php echo $rowDetail['trans_amount']; ?></div>
                                    </div>
                        <?php
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php
        }
    } else {
        ?>
        <div class="row center">
            <span style="opacity: .7; margin-top: 20px;" class="animated bounceInUp">
                No transaction this month
            </span>
        </div>
<?php
    }
}
?>