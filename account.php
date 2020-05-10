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

if ($acc_lv == 0) { // if is admin redirect to admin.php
    header('Location:admin.php');
    exit();
}
include "conn.php";
include './ajax/function.php';

$fileName = basename(__FILE__, '.php');
$sqlProfile = "SELECT * FROM user WHERE user_id = ?";

$result = mysqli_prepare($conn, $sqlProfile);
mysqli_stmt_bind_param($result, 'i', $user_id);
if (mysqli_stmt_execute($result)) {
    $results = mysqli_stmt_get_result($result);

    while ($row = mysqli_fetch_array($results)) {
        $userName = $row["user_name"];
        $userEmail = $row["user_email"];
        $accessLevel = $row["access_level"];
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>
        <?php echo ucfirst($fileName); ?> | SaveTrack - Your Best Savings Companion
    </title>
    <?php include 'import.php'; ?>
    <link rel="stylesheet" href="css/account.css">
</head>

<body>
    <?php include 'navbar.php'; ?>
    <section id="content">
        <div class="card">
            <!-- profile name -->
            <h1>Profile</h1>
            <div class="profile">
                <span>
                    <h5>Name:
                        <?php echo $userName; ?>
                    </h5>
                </span>
                <span>
                    <h5>Email:
                        <?php echo $userEmail; ?>
                    </h5>
                </span>
                <button type="button" onclick="onChangePassword()" class="btn">Change Password</button>
            </div>
        </div>
    </section>
    <script>
        const onChangePassword = () => {
            window.location.href = 'change-pass.php';
        }
    </script>
</body>

</html>