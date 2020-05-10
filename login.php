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
    <title>Login | SaveTrack - Your Best Savings Companion</title>
    <?php include './import.php'; ?>
    <link rel="stylesheet" href="./css/register-login.css">
    <script src="./js/login.js"></script>
</head>

<body>
    <div class="logo-wrapper">
        <img src="images/brand.png" alt="SaveTrack" class="logo">
        <span class="logo-name">SaveTrack</span>
    </div>

    <div class="form-wrapper">
        <span>Login Account</span>
        <div>Don't have an account? <a href="register.php">Register</a></div>
        <form action="login-submit.php" method="POST" id="login-form" class="reg-log-form" novalidate>
            <div class="center">
                <label for="email" class="input-label">
                    Email Address
                </label>
                <div class="email-wrapper">
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" tabindex="1">
                    </div>
                </div>
                <label for="password" class="input-label">
                    Password
                </label>
                <div class="password-wrapper">
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" class="password" tabindex="2">
                    </div>
                    <button type="button" class="toggle-pw" tabindex="-1">
                        <i class="fas fa-eye-slash"></i>
                    </button>
                </div>
                <div class="btn-wrapper">
                    <button type="submit" class="btn-form btn-submit" tabindex="3">Login</button>
                </div>
            </div>
        </form>
    </div>
</body>

</html>