<?php
include "conn.php";
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location:login.php');
    exit();
} else {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['user_name'];
    $family_id = isset($_SESSION['family_id']) ? $_SESSION['family_id'] : null;
    $acc_lv = $_SESSION['access_lv'];
}

if ($acc_lv == 0) {
    header('Location:admin.php');
    exit();
}
include './ajax/function.php';

$fileName = basename(__FILE__, '.php');
$sqlFamily = "SELECT user_id, user_name, user_email, access_level FROM user u  WHERE family_id = " . $family_id;

$result = mysqli_query($conn, $sqlFamily);
?>

<!DOCTYPE html>
<html>
<head>
    <title>
        <?php echo ucfirst($fileName); ?> | SaveTrack - Your Best Savings Companion
    </title>
    <?php include 'import.php'; ?>
    <script src="js/family.js"></script>
    <link rel="stylesheet" href="css/family.css">
</head>

<body>
    <?php include 'navbar.php'; ?>
    <section id="content" class="container-fluid">
        <?php if ($family_id == null) { ?>
            <div class="form-wrapper">
                <span>Register Your Family </span>
                <form action="register-family-submit.php" method="POST" id="register-form" class="reg-log-form" novalidate autocomplete="off">
                    <div class="center">
                    <label for="family_name" class="input-label">
                            Family Name
                        </label>
                        <div class="family-wrapper">
                            <div class="input-wrapper">
                                <input type="text" id="family_name" name="family_name">
                            </div>
                        </div>
                    </div>
                </form>
                <div class="btn-wrapper">
                    <button type="submit" class="btn-form btn-submit" form="register-form">Create Family</button>
                </div>
                <div class="btn-wrapper">
                    <a href="transaction.php "> <button type="button" class="btn-form btn-submit">Cancel</button></a>
                </div>
            </div>
        <?php }
        if ($family_id != null) { ?>
            <div class="">
                <table class="table">
                    <thead>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Child Summary</th>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = mysqli_fetch_array($result)) {
                            echo "<tr>";
                            echo "<td>" . $row['user_name'] . "</td>";
                            echo "<td class=''>" . $row["user_email"] . "</td>";
                            echo "<td class='summary'><a href='child-summary.php?id=" . $row['user_id'] . "'>Summary</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
    </section>
    <a href="add-family.php">
        <button type="button" id="add-trans" class="btn-eff" data-effect="click-l">
            <i class="fal fa-plus"></i>
            <span class="clicked"></span>
        </button>
    </a>
<?php } ?>
</body>


</html>