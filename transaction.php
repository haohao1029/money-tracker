<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location:login.php');
    exit();
} else {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['user_name'];
    $acc_lv = $_SESSION['access_lv'];
}

if ($acc_lv == 0) { // if is admin redirect to admin.php
    header('Location:admin.php');
    exit();
}

$fileName = basename(__FILE__, '.php');

include './conn.php';
include './ajax/function.php';

if (isset($_POST['month']) && isset($_POST['year']) && isset($_POST['day'])) {
    $month = $_POST['month'];
    $year = $_POST['year'];
    $day = $_POST['day'];
} else {
    $month = date('n');
    $year = date('Y');
    $day = date('j');
}

$date = mktime(0, 0, 0, $month, $day, $year);
?>

<!DOCTYPE html>
<html>

<head>
    <title>
        <?php echo ucfirst($fileName); ?> | SaveTrack - Your Best Savings Companion
    </title>
    <?php include './import.php'; ?>
    <link rel="stylesheet" href="./css/transaction.css">
    <script src="./js/transaction.js"></script>
</head>

<body>
    <?php include './navbar.php'; ?>
    <section id="content" class="container-fluid">
        <div class="row center control">
            <div class="trans-total col-11 col-sm-10 col-md-9 col-lg-8 col-xl-7 animated bounceInUp">
                <div class="col--title">
                    <span class="showing-month" data-value="<?php echo $date; ?>">
                        <?php echo date('F Y', $date); ?>
                    </span>
                    <div class="prev-btn-wrapper">
                        <button type="button" class="prev-month" value="-1">
                            <i class="fad fa-chevron-left"></i>
                        </button>
                    </div>
                    <div class="next-btn-wrapper">
                        <div id="current-month" class="btn-eff" value="0">
                            <i class="fad fa-dot-circle"></i>
                            <div class="clicked sm"></div>
                        </div>
                        <button type="button" class="next-month" value="1" disabled>
                            <i class="fad fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div class="col--body">
                    <?php
                    $sqlExpenses =
                        "SELECT 
                            SUM(t.trans_amount) AS 'sum_expenses'
                        FROM 
                            transaction t 
                            INNER JOIN category c ON t.category_id = c.category_id 
                            INNER JOIN wallet w ON t.wallet_id = w.wallet_id 
                            INNER JOIN user u ON w.user_id = u.user_id 
                        WHERE 
                            u.user_id = $user_id 
                            AND c.category_type = 'expenses' 
                            AND YEAR(t.trans_date) = $year 
                            AND MONTH(t.trans_date) = $month 
                            AND w.wallet_status = 1;";

                    $resultExpenses = mysqli_query($conn, $sqlExpenses);

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
                    ?>
                    <div class="expenses">
                        <div class="col--body-head">
                            Expenses (RM)
                        </div>
                        <div class="col--body-content">
                            <div class="value-wrapper">
                                <div class="value">
                                    <?php echo number_format($expenses, 2, '.', ''); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    $sqlIncome =
                        "SELECT 
                            SUM(t.trans_amount) AS 'sum_income' 
                        FROM 
                            transaction t 
                            INNER JOIN category c ON t.category_id = c.category_id 
                            INNER JOIN wallet w ON t.wallet_id = w.wallet_id 
                            INNER JOIN user u ON w.user_id = u.user_id 
                        WHERE 
                            u.user_id = $user_id
                            AND c.category_type = 'income' 
                            AND YEAR(t.trans_date) = $year 
                            AND MONTH(t.trans_date) = $month 
                            AND w.wallet_status = 1;";

                    $resultIncome = mysqli_query($conn, $sqlIncome);

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
                    ?>

                    <div class="income">
                        <div class="col--body-head">
                            Income (RM)
                        </div>
                        <div class="col--body-content">
                            <div class="value-wrapper">
                                <div class="value">
                                    <?php echo number_format($income, 2, '.', ''); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="total">
                        <div class="col--body-head">
                            Balance (RM)
                        </div>
                        <div class="col--body-content">
                            <div class="value-wrapper">
                                <div class="value">
                                    <?php echo number_format($income - $expenses, 2, '.', ''); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
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
                                <div class="date">
                                    <?php echo date('d M', strtotime($rowDay['date'])); ?>
                                </div>
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
                                                <div class="trans-detail col-9">
                                                    <div class="trans-category"><?php echo $rowDetail['category_name']; ?></div>
                                                    <div class="trans-desc"><?php echo $rowDetail['trans_desc']; ?></div>
                                                </div>
                                                <div class="trans-amount col-3"><?php echo $rowDetail['trans_amount']; ?></div>
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
                // no transaction
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
    </section>

    <div class="modal animated bounceIn fast fade" id="modal-show-trans">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-modal-close">
                        <i class="far fa-arrow-left"></i>
                    </button>
                    <h4 class="modal-title">Transaction Details</h4>
                    <button type="button" class="btn-delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="detail-category"></div>
                    <table>
                        <tr>
                            <td>Date</td>
                            <td class="detail-date"></td>
                        </tr>
                        <tr>
                            <td>Type</td>
                            <td class="detail-type"></td>
                        </tr>
                        <tr>
                            <td>Wallet</td>
                            <td class="detail-wallet"></td>
                        </tr>
                        <tr>
                            <td>Amount (RM)</td>
                            <td class="detail-amount"></td>
                        </tr>
                        <tr>
                            <td>Description</td>
                            <td class="detail-desc"></td>
                        </tr>
                    </table>
                </div>
                <div class="bottom-btn">
                    <button type="button" id="btn-edit" class="btn-eff btn-form btn-edit" data-effect="click-l">
                        <i class="fas fa-pen"></i>
                        <span class="clicked"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal animated fade fadeInRight fast" id="modal-edit-trans" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-modal-close btn-eff">
                        <i class="far fa-arrow-left"></i>
                        <span class="clicked"></span>
                    </button>
                    <h4 class="modal-title">Edit Transaction</h4>
                </div>
                <div class="modal-body">
                    <form action="edit-trans.php" method="POST" id="form-edit-trans" class="form-trans" novalidate autocomplete="off">
                        <div class="new-type row">
                            <span class="input-title col-4">Type</span>
                            <select name="edit-type" id="edit-type" class="col-8 form-input">
                                <option value="expenses">Expenses</option>
                                <option value="income">Income</option>
                            </select>
                        </div>
                        <div class="new-category row">
                            <span class="input-title col-4">Category</span>
                            <select name="edit-category" id="edit-category" class="col-8 form-input"></select>
                        </div>
                        <div class="new-wallet row">
                            <span class="input-title col-4">Wallet</span>
                            <select name="edit-wallet" id="edit-wallet" class="col-8 form-input"></select>
                        </div>
                        <div class="new-amount row">
                            <span class="input-title col-4">Amount</span>
                            <div class="col-8 input-amount row" style="margin: 0">
                                <span class="col-2">RM</span>
                                <input type="text" name="edit-amount" id="edit-amount" class="col-10 form-input" placeholder="Enter amount" value="">
                            </div>
                        </div>
                        <div class="new-date row">
                            <span class="input-title col-4">Date</span>
                            <input type="date" name="edit-date" id="edit-date" class="col-8 form-input input-date">
                        </div>
                        <div class="new-desc row">
                            <span class="input-title col-12">Description</span>
                            <textarea name="edit-description" id="edit-description" class="col-12" rows="3" placeholder="Enter description (optional)"></textarea>
                        </div>
                        <input type="hidden" name="trans_id" id="trans_id">
                        <input type="hidden" name="date_edited" id="date_edited" value="false">
                    </form>
                </div>
                <div class="bottom-btn">
                    <button type="reset" form="form-edit-trans" id="btn-cancel-edit" class="btn-cancel btn-eff btn-modal-close btn-form" data-effect="click-l">
                        <i class="far fa-times"></i>
                        <span class="clicked"></span>
                    </button>
                    <button type="submit" form="form-edit-trans" id="btn-save-edit" class="btn-save btn-eff btn-form" data-effect="click-l">
                        <i class="far fa-check"></i>
                        <span class="clicked"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal animated bounceInUp fast fade" id="modal-add-trans">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="reset" form="form-add-trans" class="btn-modal-close">
                        <i class="far fa-arrow-left"></i>
                    </button>
                    <h4 class="modal-title">New Transaction</h4>
                </div>
                <div class="modal-body">
                    <form action="add-trans.php" method="POST" id="form-add-trans" class="form-trans" novalidate autocomplete="off">
                        <div class="new-type row">
                            <span class="input-title col-4">Type</span>
                            <select name="type" id="type" class="col-8 form-input">
                                <option selected disabled class="select-placeholder">- Select a type -</option>
                                <option value="expenses">Expenses</option>
                                <option value="income">Income</option>
                            </select>
                        </div>
                        <div class="new-category row">
                            <span class="input-title col-4">Category</span>
                            <select name="category" id="category" class="col-8 form-input" disabled>
                                <option disabled selected class="select-placeholder">- Select a type first -</option>
                            </select>
                        </div>
                        <div class="new-wallet row">
                            <span class="input-title col-4">Wallet</span>
                            <select name="wallet" id="wallet" class="col-8 form-input">
                                <option selected disabled class="select-placeholder">- Select a wallet -</option>
                            </select>
                        </div>
                        <div class="new-amount row">
                            <span class="input-title col-4">Amount</span>
                            <div class="col-8 input-amount row" style="margin: 0">
                                <span class="col-2">RM</span>
                                <input type="text" name="amount" id="amount" class="col-10 form-input" placeholder="Enter amount">
                            </div>
                        </div>
                        <div class="new-date row">
                            <span class="input-title col-4">Date</span>
                            <input type="text" value="today" name="date" id="date" class="col-8 form-input input-date" data-value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="new-desc row">
                            <span class="input-title col-12">Description</span>
                            <textarea name="description" id="description" class="col-12" rows="3" placeholder="Enter description (optional)"></textarea>
                        </div>
                    </form>
                </div>
                <div class="bottom-btn">
                    <button type="reset" form="form-add-trans" id="btn-cancel-add" class="btn-eff btn-modal-close btn-cancel btn-form" data-effect="click-l">
                        <i class="far fa-times"></i>
                        <span class="clicked"></span>
                    </button>
                    <button type="submit" form="form-add-trans" id="btn-save-add" class="btn-eff btn-save btn-form" data-effect="click-l">
                        <i class="far fa-check"></i>
                        <span class="clicked"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade modal-small" id="modal-confirm-delete" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Delete Transaction</h4>
                </div>
                <div class="modal-body">
                    <div style="margin-bottom:15px;">Would you like to delete this transaction record?</div>
                    <strong>Note: </strong>You can't undo this action.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-dismiss="modal">Cancel</button>
                    <button type="button" id="confirm-delete" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade modal-small" id="modal-exceed" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Insufficient Balance</h4>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" style="background-color:var(--colorMain); color:#fff;padding-top:.2rem;padding-bottom:.2rem;border:none" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <button type="button" id="add-trans" class="btn-eff" data-effect="click-l">
        <i class="fal fa-plus"></i>
        <span class="clicked"></span>
    </button>
</body>

</html>
