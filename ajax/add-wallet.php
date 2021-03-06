<?php
function checkEmpty($var)
{
    return ((empty($var)) || ctype_space($var));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $balance = $_POST['initial_balance'];

    if (checkEmpty($name)) {
        die('empty');
    }
} else {
    header('Location:wallet.php');
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

$sql = mysqli_prepare($conn, "INSERT INTO wallet (wallet_name, user_id, wallet_bal) VALUES (?, ?, ?);");
mysqli_stmt_bind_param($sql, 'sid', $name, $user_id, $balance);
if (mysqli_stmt_execute($sql) && !(mysqli_affected_rows($conn) <= 0)) {
    // fetch newly created wallet's id
    $sqlId = "SELECT wallet_id FROM wallet WHERE user_id = $user_id AND wallet_name = '$name' AND wallet_bal = $balance ORDER BY wallet_id DESC LIMIT 1;";
    $resultId = mysqli_query($conn, $sqlId);
    $rowId = mysqli_fetch_array($resultId);
    $id = $rowId['wallet_id'];
?>
    <div class="wallet-item row">
        <div class="col-6 wallet-name">
            <?php echo $name; ?>
        </div>
        <div class="col-5 wallet-balance">
            <?php echo number_format($balance, 2, '.', ''); ?>
        </div>
        <div class="col-1 show-detail">
            <i class="fas fa-caret-down"></i>
        </div>
    </div>
    <div class="wallet-detail" data-value="<?php echo $id; ?>">
        <div class="detail-wrapper">
            <div class="empty-plan" style="min-height: 45px; display: flex; align-items: center; justify-content: center; opacity: .7">This wallet doesn't have any plan.</div>
        </div>
        <div class="button-wrapper">
            <button type="button" class="btn-custom btn-delete btn-eff">
                <i class="fas fa-trash"></i>
                <span>Delete</span>
                <span class="clicked"></span>
            </button>
            <button type="button" class="btn-custom btn-edit btn-eff">
                <i class="fas fa-pen"></i>
                <span>Edit</span>
                <span class="clicked"></span>
            </button>
            <button type="button" class="btn-custom btn-add-plan btn-eff">
                <i class="fal fa-plus"></i>
                <span>Plan</span>
                <span class="clicked"></span>
            </button>
        </div>
    </div>
<?php
} else {
    echo 'fail';
}
mysqli_stmt_close($sql);
