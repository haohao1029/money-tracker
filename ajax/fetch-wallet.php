<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location:login.php');
    exit();
} else {
    $user_id = $_SESSION['user_id'];
}

include '../conn.php';

$sql = mysqli_prepare($conn, "SELECT wallet_id, wallet_name FROM wallet WHERE user_id = ? AND wallet_status = 1 ORDER BY wallet_name ASC;");
mysqli_stmt_bind_param($sql, 'i', $user_id);
if (mysqli_stmt_execute($sql)) {
    $result = mysqli_stmt_get_result($sql);
    if (!(mysqli_num_rows($result) <= 0)) {
        while ($row = mysqli_fetch_array($result)) {
?>
            <option value="<?php echo $row['wallet_id']; ?>"><?php echo $row['wallet_name']; ?></option>
<?php
        }
    } else {
        die('error');
    }
} else {
    die('error');
}
?>