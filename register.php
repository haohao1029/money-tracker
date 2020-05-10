<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location:transaction.php');
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Register Account | SaveTrack - Your Best Savings Companion</title>
    <?php include './import.php'; ?>
    <link rel="stylesheet" href="./css/register-login.css">
    <script src="./js/register.js"></script>
</head>

<body>
    <div class="logo-wrapper">
        <img src="images/brand.png" alt="SaveTrack" class="logo">
        <span class="logo-name">SaveTrack</span>
    </div>

    <div class="form-wrapper">
        <span>Register Account</span>
        <div>Already have an account? <a href="login.php">Login</a></div>
        <form action="register-submit.php" method="POST" id="register-form" class="reg-log-form" novalidate autocomplete="off">
            <div class="center">
                <label for="username" class="input-label">
                    Username
                </label>
                <div class="username-wrapper">
                    <div class="input-wrapper">
                        <input type="text" id="username" name="username">
                    </div>
                </div>
                <label for="email" class="input-label">
                    Email Address
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
            <button type="submit" class="btn-form btn-submit" form="register-form">Register</button>
        </div>
    </div>
</body>

</html>