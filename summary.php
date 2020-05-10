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

$fileName = basename(__FILE__, '.php');
include './ajax/function.php';
?>

<!DOCTYPE html>
<html>

<head>
    <title>
        <?php echo ucfirst($fileName); ?> | SaveTrack - Your Best Savings Companion
    </title>
    <?php include 'import.php'; ?>
    <link rel="stylesheet" href="css/summaries.css">
</head>

<body>
    <?php include 'navbar.php'; ?>
    <section id="content">
        <div class="container">
            <a href="day-summary.php">
                <div class="box">
                    <div class="icon">
                    </div>
                    <div class="contents">
                        <h3>See Your Daily Summary</h3>
                    </div>
                </div>
            </a>
            <a href="month-summary.php">
                <div class="box">
                    <div class="icon">
                    </div>
                    <div class="contents">
                        <h3>See Your Monthly Summary</h3>
                    </div>
                </div>
            </a>
            <a href="year-summary.php">
                <div class="box">
                    <div class="icon">
                        </div>
                        <div class="contents">
                            <h3>See Your Yearly Summary</h3>
                        </div>
                    </div>
                </a>
                <?php if ($family_id != null && $acc_lv == 1) { ?>
                <a href="day-family-summary.php">
                    <div class="box">
                        <div class="icon">
                        </div>
                        <div class="contents">
                            <h3>See Your Family Daily Summary</h3>
                        </div>
                    </div>
                </a>
                <a href="month-family-summary.php">
                    <div class="box">
                        <div class="contents">
                            <h3>See Your Family Monthly Summary</h3>
                        </div>
                    </div>
                </a>
                <a href="year-family-summary.php">
                    <div class="box">
                        <div class="contents">
                            <h3>See Your Family Yearly Summary</h3>
                        </div>
                    </div>
                </a>
            <?php } ?>

        </div>
    </section>
</body>

</html>