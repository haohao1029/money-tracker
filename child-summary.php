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
$uid = $_GET['id'];
if ($acc_lv == 0) {
    header('Location:admin.php');
    exit();
}
include "conn.php";
include './ajax/function.php';

$fileName = basename(__FILE__, '.php');
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
            <?php
            echo   "<a href='day-child-summary.php?id=" . $uid . "'>"
            ?>
            <div class="box">
                <div class="icon">
                </div>
                <div class="contents">
                    <h3>See Your Daily Summary</h3>

                </div>
            </div>
            </a>
            <input type="hidden" value="<?php echo  $uid ?>" id="uid">
            <?php
            echo   "<a href='month-child-summary.php?id=" . $uid . "'>"
            ?>
            <div class="box">
                <div class="icon">
                </div>
                <div class="contents">
                    <h3>See Your Monthly Summary</h3>
                </div>
            </div>
            </a>
            <?php
            echo   "<a href='year-child-summary.php?id=" . $uid . "'>"
            ?>
            <div class="box">
                <div class="icon">
                </div>
                <div class="contents">
                    <h3>See Your Yearly Summary</h3>
                </div>
            </div>
            </a>
        </div>
    </section>
</body>

</html>