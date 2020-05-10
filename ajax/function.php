<?php
function checkEmpty($var)
{
    return ((empty($var)) || ctype_space($var));
}

/**
 * Calculate percentage of amount spent based on the budget amount
 * 
 * @param double $spent Amount spent
 * @param double $budget Budget amount
 * @return double Percentage
 */
function calcPercentSpent($spent, $budget)
{
    $percentage = $spent / $budget * 100;
    return round($percentage, 0);
}

/**
 * Everything related to plan system. Update the alert if available
 * 
 * @param mysqli $conn Connection
 * @param int $wallet Wallet ID
 * @param int $categ Category ID
 * 
 * @return null|array If plan alert is set
 * 
 * Get amount spent         - $var[$id]['amount_spent']
 * 
 * Get percentage spent     - $var[$id]['percentage_spent']
 * 
 * Get plan id of the alert - $var[$id]['id']
 */
function plan($conn, $wallet, $categ)
{
    $count = 0;
    // check if any plan with the wallet and category exists
    $sqlCheckPlan = mysqli_prepare(
        $conn,
        "SELECT 
            p.plan_id, 
            p.plan_amount, 
            p.plan_alert, 
            p.start_date,
            p.end_date
        FROM 
            plan p
            INNER JOIN wallet w ON p.wallet_id = w.wallet_id
        WHERE 
            p.category_id = ? 
            AND p.wallet_id = ?
            AND w.wallet_status = 1;"
    );
    mysqli_stmt_bind_param($sqlCheckPlan, 'ii', $categ, $wallet);
    if (mysqli_stmt_execute($sqlCheckPlan)) {
        $resultCheckPlan = mysqli_stmt_get_result($sqlCheckPlan);
        if (mysqli_num_rows($resultCheckPlan) > 0) {
            $arrayOfArrayPlan = array();
            while ($rowCheckPlan = mysqli_fetch_array($resultCheckPlan)) {
                $arrayPlan = array(
                    'id'            => $rowCheckPlan['plan_id'],
                    'plan_amount'   => $rowCheckPlan['plan_amount'],
                    'plan_alert'    => $rowCheckPlan['plan_alert'],
                    'start_date'    => $rowCheckPlan['start_date'],
                    'end_date'      => $rowCheckPlan['end_date']
                );
                array_push($arrayOfArrayPlan, $arrayPlan);
            }
            $count = count($arrayOfArrayPlan);
        }
    }
    mysqli_stmt_close($sqlCheckPlan);

    // if have plan then do
    if ($count) {
        // calculate % spent on every row of data (every plan)
        for ($i = 0; $i < $count; $i++) {
            $id             = $arrayOfArrayPlan[$i]['id'];
            $plan_amount    = $arrayOfArrayPlan[$i]['plan_amount'];
            $plan_alert     = $arrayOfArrayPlan[$i]['plan_alert'];
            $start_date     = $arrayOfArrayPlan[$i]['start_date'];
            $end_date       = $arrayOfArrayPlan[$i]['end_date'];

            // get amount spent
            $amountSpent = getAmountSpent($conn, $categ, $wallet, $start_date, $end_date);

            // calculate the percentage spent
            $percentSpent = calcPercentSpent($amountSpent, $plan_amount);

            // if alert set and percentSpent more than alert, put into session
            if ($plan_alert && ($percentSpent >= $plan_alert)) {
                if (array_key_exists($id, $_SESSION['alert'])) {
                    removeAlert($id);
                }
                $_SESSION['alert'][$id] = $percentSpent;
            } else {
                removeAlert($id);
            }

            // return value in array form
            $return[$id] = array(
                'id'                => $id,
                'amount_spent'      => $amountSpent,
                'percentage_spent'  => $percentSpent
            );
        }
    }
    return isset($return) ? $return : null;
}

/**
 * Get the amount spent
 * 
 * @param mysqli $conn Connection
 * @param int $categ Category ID
 * @param int $wallet Wallet ID
 * @param string $start_date Start date of plan
 * @param string $end_date End date of plan
 */
function getAmountSpent($conn, $categ, $wallet, $start_date, $end_date)
{
    if ($end_date) { // end_date is specified
        $sqlAmountSpent = mysqli_prepare(
            $conn,
            "SELECT SUM(trans_amount) AS sum_spent FROM transaction 
                    WHERE 
                    category_id = ? 
                    AND wallet_id = ? 
                    AND (DATE(trans_date) BETWEEN ? AND ?);"
        );
        mysqli_stmt_bind_param($sqlAmountSpent, 'iiss', $categ, $wallet, $start_date, $end_date);
    } else { // end_date is NULL
        $sqlAmountSpent = mysqli_prepare(
            $conn,
            "SELECT 
                SUM(trans_amount) AS sum_spent FROM transaction 
            WHERE 
                category_id = ? 
                AND wallet_id = ? 
                AND (DATE(trans_date) BETWEEN ? AND DATE_ADD(?, INTERVAL 30 DAY));"
        );
        mysqli_stmt_bind_param($sqlAmountSpent, 'iiss', $categ, $wallet, $start_date, $start_date);
    }
    if (mysqli_stmt_execute($sqlAmountSpent)) {
        $resultAmountSpent = mysqli_stmt_get_result($sqlAmountSpent);
        if (mysqli_num_rows($resultAmountSpent) > 0) {
            $amountSpent = mysqli_fetch_array($resultAmountSpent)['sum_spent'];
        } else {
            $amountSpent = 0;
        }
    } else {
        $amountSpent = 0;
    }
    mysqli_stmt_close($sqlAmountSpent);

    return $amountSpent;
}

/**
 * @param int $plan_id ID of plan whose alert to be removed from $_SESSION
 */
function removeAlert($plan_id)
{
    unset($_SESSION['alert'][$plan_id]);
}

/**
 * @param mysqli $conn Connection
 * @param int $user_id User ID in session
 */
function refreshNoti($conn, $user_id)
{
    if ($_SESSION['access_lv'] == 1 && isset($_SESSION['family_id'])) {
        $fam_id = $_SESSION['family_id'];
        $sqlPlan =
            "SELECT * 
        FROM
            plan p
            INNER JOIN wallet w ON p.wallet_id = w.wallet_id
            INNER JOIN user u ON w.user_id = u.user_id
        WHERE
            u.family_id = $fam_id
            AND w.wallet_status = 1;";
        $resultPlan = mysqli_query($conn, $sqlPlan);
    } else {
        $sqlPlan =
            "SELECT * 
    FROM 
        plan p 
        INNER JOIN wallet w ON p.wallet_id = w.wallet_id 
    WHERE 
        w.user_id = $user_id
        AND w.wallet_status = 1;";
        $resultPlan = mysqli_query($conn, $sqlPlan);
    }

    if (mysqli_num_rows($resultPlan) > 0) {
        while ($rowPlan = mysqli_fetch_array($resultPlan)) {
            $wallet = $rowPlan['wallet_id'];
            $categ = $rowPlan['category_id'];
            plan($conn, $wallet, $categ);
        }
    }
}

/**
 * @param int $alertCount Number of alert in session
 * 
 * @return string  Notification's HTML element
 */
function getNotiEl($conn, $alertCount)
{
    $joinNotiEl = '';
    if ($alertCount) {
        foreach ($_SESSION['alert'] as $id => $percent) {
            $sqlNoti = "SELECT p.plan_name, w.wallet_name FROM plan p INNER JOIN wallet w ON p.wallet_id = w.wallet_id WHERE p.plan_id = $id;";
            $resultNoti = mysqli_query($conn, $sqlNoti);
            $row = mysqli_fetch_array($resultNoti);
            $planName = $row['plan_name'];
            $walletName = $row['wallet_name'];

            $notiEl =
                "<div class=\"noti-item\" data-value=\"$id\">
                <div class=\"percent-wrapper row\">
                    <span class=\"col-3\" style=\"padding:0;font-weight:700\">Alert</span><span style=\"color: #f00;margin-right: 6px\">$percent%</span> of budget spent.
                </div>
                <div class=\"plan-wrapper row\">
                    <span class=\"col-3\" style=\"padding:0;font-weight:700\">Plan</span>
                    $planName
                </div>
                <div class=\"wallet-wrapper row\">
                    <span class=\"col-3\" style=\"padding:0;font-weight:700\">Wallet</span>
                    $walletName
                </div>
            </div>";
            $joinNotiEl .= $notiEl;
        }
    } else {
        $joinNotiEl = '<div class="noti-empty">You have no notifications.</div>';
    }
    return $joinNotiEl;
}
