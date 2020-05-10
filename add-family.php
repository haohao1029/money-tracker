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
?>

<!DOCTYPE html>
<html>

<head>
    <title>
        <?php echo ucfirst($fileName); ?> | SaveTrack - Your Best Savings Companion
    </title>
    <?php include 'import.php'; ?>
    <link rel="stylesheet" href="css/register-family.css">
    <script src="js/child-register.js"></script>
</head>

<body>
    <?php include 'navbar.php'; ?>
    <div class="logo-wrapper">
        <img src="images/brand.png" alt="SaveTrack" class="logo">
        <span class="logo-name">SaveTrack</span>
    </div>

    <div class="form-wrapper">
        <span>Register Child Account</span>
        <form action="ajax/register-child-submit.php" method="POST" id="register-form" class="reg-log-form" novalidate autocomplete="off">
            <div class="center">
                <label for="username" class="input-label">
                    Child Username
                </label>
                <div class="username-wrapper">
                    <div class="input-wrapper">
                        <input type="text" id="username" name="username">
                    </div>
                </div>
                <label for="email" class="input-label">
                    Child Email Address
                </label>
                <div class="email-wrapper">
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email">
                    </div>
                </div>
                <label for="password" class="input-label">
                    Password
                </label>
                <div class="password-wrapper">
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" class="password">
                    </div>
                    <button type="button" class="toggle-pw" tabindex="-1">
                        <i class="fas fa-eye-slash"></i>
                    </button>
                </div>
                <label for="confirm_password" class="input-label">
                    Confirm Password
                </label>
                <div class="password-wrapper">
                    <div class="input-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" class="password">
                    </div>
                    <button type="button" class="toggle-pw" tabindex="-1">
                        <i class="fas fa-eye-slash"></i>
                    </button>
                </div>
            </div>
        </form>
        <div class="btn-wrapper">
            <button type="submit" class="btn-form btn-submit register" form="register-form">Register</button>
        </div>
        <div class="btn-wrapper">
            <a href="family.php "> <button type="button" class="btn-form btn-submit">Cancel</button></a>
        </div>
    </div>
</body>
</html>