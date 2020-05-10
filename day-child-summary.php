<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location:login.php');
    exit();
} else {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['user_name'];
    $acc_lv = $_SESSION['access_lv'];
    $family_id = isset($_SESSION['family_id']) ? $_SESSION['family_id'] : null;
}

if ($acc_lv == 0) {
    header('Location:admin.php');
    exit();
}
include "conn.php";
include './ajax/function.php';

$fileName = basename(__FILE__, '.php');

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

$uid = $_GET['id'];

?>

<?php


//IncomeBar
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
        AND YEAR(t.trans_date) = ? 
        AND MONTH(t.trans_date) = ?
        AND DAY(t.trans_date) = ?"
);
mysqli_stmt_bind_param($sqlIncomeBar, 'iiii', $uid, $year, $month, $day);
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
        AND YEAR(t.trans_date) = ? 
        AND MONTH(t.trans_date) = ?
        AND DAY(t.trans_date) = ?"
);
mysqli_stmt_bind_param($sqlExpenseBar, 'iiii', $uid, $year, $month, $day);
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
        AND MONTH(t.trans_date) = ?
        AND DAY(t.trans_date) = ?
    GROUP BY 
        category_name"
);
mysqli_stmt_bind_param($sqlIncomePie, 'iiii', $uid, $year, $month, $day);
if (mysqli_stmt_execute($sqlIncomePie)) {
    $resultIncomePie = mysqli_stmt_get_result($sqlIncomePie);

    while ($row = mysqli_fetch_array($resultIncomePie)) {
        $income = $row['incomes'];
        $pcategory = $row['category_name'];

        $incomes = $incomes . '"' . $income . '",';
        $pcategories = $pcategories . '"' . $pcategory . '",';
    }
    $incomes = trim($incomes, ",");
    $pcategories = trim($pcategories, ",");
}
mysqli_stmt_close($sqlIncomePie);


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
        AND MONTH(t.trans_date) = ?
        AND DAY(t.trans_date) = ?
    GROUP BY 
        category_name"
);
mysqli_stmt_bind_param($sqlExpensePie, 'iiii', $uid, $year, $month, $day);
if (mysqli_stmt_execute($sqlExpensePie)) {
    $resultExpensePie = mysqli_stmt_get_result($sqlExpensePie);
    while ($row = mysqli_fetch_array($resultExpensePie)) {
        $expense = $row['expenses'];
        $ecategory = $row['category_name'];

        $expenses = $expenses . '"' . $expense . '",';
        $ecategories = $ecategories . '"' . $ecategory . '",';
    }
    $expenses = trim($expenses, ",");
    $ecategories = trim($ecategories, ",");
}
mysqli_stmt_close($sqlExpensePie);
?>
<!DOCTYPE html>
<html>

<head>
    <title>
        <?php echo ucfirst($fileName); ?> | SaveTrack - Your Best Savings Companion
    </title>
    <?php include 'import.php'; ?>
    <link rel="stylesheet" href="css/summary.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
    <script src="js/day-child-summary.js"></script>

</head>

<body>
    <input type="hidden" value="<?php echo  $uid ?>" id="uid">
    <?php include 'navbar.php'; ?>
    <section id="content">
        <div class="row center control">
            <div class="trans-total col-11 col-sm-10 col-md-9 col-lg-8 col-xl-7 animated bounceInUp">

                <div class="col--title">
                    <span class="showing-month" data-value="<?php echo $date; ?>">
                        <?php echo date('d F Y', $date); ?>
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
                    <div class="row center">
                        <div class="old">
                            <div class="title">
                                <h5>Bar Chart</h5>
                            </div>
                            <div id="error1" class="error">
                                <p>Income and Expense does not exist</p>
                            </div>
                            <div class="chart1">
                                <canvas id="bar"></canvas>
                            </div>
                            <div class="title">
                                <h5>Income Chart</h5>
                            </div>
                            <div id="error2" class="error">
                                <p>Income does not exist</p>
                            </div>
                            <div class="chart2">
                                <canvas id="pie"></canvas>
                            </div>
                            <div class="title">
                                <h5>Expense Chart</h5>
                            </div>
                            <div id="error3" class="error">
                                <p>Expense does not exist</p>
                            </div>
                            <div class="chart3">
                                <canvas id="epie"></canvas>
                            </div>
                        </div>
                        <div class="new">
                            <div class="title">
                                <h5>Bar Chart</h5>
                            </div>
                            <div id="error4" class="error">
                                <p>Income and Expense does not exist</p>
                            </div>
                            <div class="chart4">
                                <canvas id="newBar"></canvas>
                            </div>
                            <div class="title">
                                <h5>Income Chart</h5>
                            </div>
                            <div id="error5" class="error">
                                <p>Income does not exist</p>
                            </div>
                            <div class="chart5">
                                <canvas id="newIncomePie"></canvas>
                            </div>
                            <div class="title">
                                <h5>Expense Chart</h5>
                            </div>
                            <div id="error6" class="error">
                                <p>Expense does not exist</p>
                            </div>
                            <div class="chart6">
                                <canvas id="newExpensePie"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>
    <script>
        window.onload = function() {
            $("#error1").hide();
            $("#error2").hide();
            $("#error3").hide();
            $(".new").hide();
            //bar
            let arrBar = [];
            arrBar.push(<?php echo $incomeBar; ?>)
            arrBar.push(<?php echo $expenseBar; ?>)
            if (arrBar.length == []) {
                $("#error1").show();
                $(".chart1").hide();
            } else {
                let bar = document.getElementById("bar").getContext('2d');
                labels = ["incomes", "expenses"];
                barColors = [];
                hoverBarColors = [];
                for (i = 0; i < 2; i++) {
                    barColors.push(getColor());
                }
                for (i = 0; i < 2; i++) {
                    hoverBarColors.push(getColor());
                }

                let barChart = new Chart(bar, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: arrBar,
                            backgroundColor: barColors,
                            hoverBackgroundColor: hoverBarColors
                        }]
                    },
                    options: {
                        legend: {
                            display: false
                        },
                        tooltips: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return tooltipItem.yLabel;
                                }
                            }
                        },
                        responsive: true, // Instruct chart js to respond nicely.
                        maintainAspectRatio: false, // Add to prevent default behaviour of full-width/height 
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true
                                }
                            }]
                        }
                    }
                });
            }
            //income pie
            let pie = document.getElementById("pie").getContext('2d');


            icategory = [<?php echo $pcategories; ?>];
            icolors = []
            hicolors = []
            if (icategory == "") {
                $("#error2").show();
                $("#pie").hide();
            } else {

                for (i = 0; i < icategory.length; i++) {
                    icolors.push(getColor());
                }
                for (i = 0; i < icategory.length; i++) {
                    hicolors.push(getColor());
                }

                let pieChart = new Chart(pie, {
                    type: 'pie',
                    data: {
                        labels: [<?php echo $pcategories; ?>],
                        datasets: [{
                            data: [<?php echo $incomes; ?>],
                            backgroundColor: icolors,
                            hoverBackgroundColor: hicolors
                            // Specify the data values array
                        }]
                    },
                    options: {
                        responsive: true, // Instruct chart js to respond nicely.
                        maintainAspectRatio: false, // Add to prevent default behaviour of full-width/height 
                    }
                });

            }
            //expense pie
            ecategory = [<?php echo $ecategories; ?>];
            ecolors = []
            hecolors = []
            if (ecategory == "") {
                $("#error3").show();
                $("#epie").hide();
            } else {

                for (i = 0; i < ecategory.length; i++) {
                    ecolors.push(getColor());
                }
                for (i = 0; i < ecategory.length; i++) {
                    hecolors.push(getColor());
                }

                let epie = document.getElementById("epie").getContext('2d');

                let epieChart = new Chart(epie, {
                    type: 'pie',
                    data: {
                        labels: [<?php echo $ecategories; ?>],
                        datasets: [{
                            data: [<?php echo $expenses; ?>],
                            backgroundColor: ecolors,
                            hoverBackgroundColor: hecolors // Specify the data values array
                        }]
                    },
                    options: {
                        responsive: true, // Instruct chart js to respond nicely.
                        maintainAspectRatio: false, // Add to prevent default behaviour of full-width/height 
                    }
                });
            }

            function getColor() {
                return "hsl(" + 360 * Math.random() + ',' +
                    (15 + 70 * Math.random()) + '%,' +
                    (55 + 10 * Math.random()) + '%)'
            }
        }
    </script>
</body>

</html>