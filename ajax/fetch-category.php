<?php
if (!isset($_POST['trans_type'])) {
    header('Location:transaction.php');
    exit();
}

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location:login.php');
    exit();
} else {
    $user_id = $_SESSION['user_id'];
}

include '../conn.php';

$type = $_POST['trans_type'];

$sql = mysqli_prepare($conn, "SELECT category_id, category_name FROM category WHERE category_type = ? ORDER BY category_name ASC;");
mysqli_stmt_bind_param($sql, 's', $type);
if (mysqli_stmt_execute($sql)) {
    $result = mysqli_stmt_get_result($sql);
    if (!(mysqli_num_rows($result) <= 0)) {
        while ($row = mysqli_fetch_array($result)) {
?>
            <option value="<?php echo $row['category_id']; ?>"><?php echo ucfirst($row['category_name']) ?></option>
<?php
        }
    } else {
        die('error');
    }
} else {
    die('error');
}
mysqli_stmt_close($sql);
?>