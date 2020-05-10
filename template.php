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

if ($acc_lv == 0) {
    header('Location:admin.php');
    exit();
}

$fileName = basename(__FILE__, '.php');
include './conn.php';
?>

<!DOCTYPE html>
<html>

<head>
    <title>
        <?php echo ucfirst($fileName); ?> | SaveTrack - Your Best Savings Companion
    </title>
    <?php include './import.php'; ?>
</head>

<body>
    <?php include './navbar.php'; ?>
    <section id="content">

    </section>
</body>

</html>