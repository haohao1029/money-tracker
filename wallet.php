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

$fileName = basename(__FILE__, '.php');

include './conn.php';
include './ajax/function.php';
?>

<!DOCTYPE html>
<html>

<head>
    <title>
        <?php echo ucfirst($fileName); ?> | SaveTrack - Your Best Savings Companion
    </title>
    <?php include './import.php'; ?>
    <link rel="stylesheet" href="./css/wallet.css">
    <script src="./js/wallet.js"></script>
</head>

<body>
    <?php include './navbar.php'; ?>
    <section id="content" class="container-fluid">
        <div class="row center">
            <div class="user-wallet col-11 col-sm-10 col-md-9 col-lg-8 col-xl-7 animated bounceInUp">
                <div class="card--header">
                    My Wallet(s)
                    <?php if ($acc_lv == 1) { ?>
                        <button type="button" id="new-wallet" class="btn-eff btn-add">
                            <i class="fal fa-plus"></i>
                            New
                            <span class="clicked"></span>
                        </button>
                    <?php } ?>
                </div>
                <div class="card--content">
                    <?php
                    $sqlWallet = "SELECT wallet_id, wallet_name, wallet_bal FROM wallet WHERE user_id = $user_id AND wallet_status = 1;";
                    $resultWallet = mysqli_query($conn, $sqlWallet);

                    if (!mysqli_num_rows($resultWallet) <= 0) {
                    ?>
                        <div class="table-head row">
                            <div class="col-6 head-cell">
                                Wallet
                            </div>
                            <div class="col-5 head-cell">
                                Balance (RM)
                            </div>
                        </div>

                        <?php
                        while ($rowWallet = mysqli_fetch_array($resultWallet)) {
                        ?>
                            <div class="wallet-item row">
                                <div class="col-6 wallet-name">
                                    <?php echo $rowWallet['wallet_name']; ?>
                                </div>
                                <div class="col-5 wallet-balance">
                                    <?php echo $rowWallet['wallet_bal']; ?>
                                </div>
                                <div class="col-1 show-detail">
                                    <i class="fas fa-caret-down"></i>
                                </div>
                            </div>
                            <div class="wallet-detail" data-value="<?php echo $rowWallet['wallet_id']; ?>">
                                <div class="detail-wrapper">
                                    <?php
                                    $sqlPlan = mysqli_prepare($conn, "SELECT * FROM plan WHERE wallet_id = ? ORDER BY plan_name ASC;");
                                    mysqli_stmt_bind_param($sqlPlan, 'i', $rowWallet['wallet_id']);
                                    if (mysqli_stmt_execute($sqlPlan)) {
                                        $resultPlan = mysqli_stmt_get_result($sqlPlan);
                                        if (!(mysqli_num_rows($resultPlan) <= 0)) {
                                            while ($rowPlan = mysqli_fetch_array($resultPlan)) {
                                                $amountSpent = getAmountSpent($conn, $rowPlan['category_id'], $rowWallet['wallet_id'], $rowPlan['start_date'], $rowPlan['end_date']);

                                                $percent = calcPercentSpent($amountSpent, $rowPlan['plan_amount']);
                                    ?>
                                                <div class="plan-item btn-eff row" data-value="<?php echo $rowPlan['plan_id']; ?>">
                                                    <div class="plan-name col-7-5 col-sm-9 col-md-9 col-lg-9-5 col-xl-10">
                                                        <?php echo $rowPlan['plan_name']; ?>
                                                    </div>
                                                    <div class="spent row col-4-5 col-sm-3 col-md-3 col-lg-2-5 col-xl-2">
                                                        <div class="col-5">Spent:</div>
                                                        <span class="col-7"><?php echo $percent . ' '; ?>%</span>
                                                    </div>
                                                    <div class="clicked"></div>
                                                </div>
                                            <?php
                                            }
                                        } else {
                                            // no plan
                                            ?>
                                            <div class="empty-plan">
                                                This wallet doesn't have any budget plan.
                                            </div>
                                    <?php
                                        }
                                        mysqli_stmt_close($sqlPlan);
                                    }
                                    ?>
                                </div>
                                <?php if ($acc_lv == 1) { ?>
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
                                <?php } ?>
                            </div>
                        <?php
                        }
                    } else {
                        ?>
                        <div class="wallet-item row" style="justify-content: center; font-weight: normal; font-size: 16px; opacity:.7; margin-top: 10px;">No wallet</div>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>

        <?php
        // family members' wallet 
        if ($family_id && $acc_lv == 1) {
            // check if the user has any family member
            $count = 0;
            $sqlCheckFam = mysqli_prepare($conn, "SELECT COUNT(user_id) AS 'count' FROM user WHERE family_id = ? AND access_level = 2 AND user_status = 1;");
            mysqli_stmt_bind_param($sqlCheckFam, 'i', $family_id);
            if (mysqli_stmt_execute($sqlCheckFam)) {
                $resultCheckFam = mysqli_stmt_get_result($sqlCheckFam);
                $count = mysqli_fetch_array($resultCheckFam)['count'];
            }

            if (!$count) { // no family member
        ?>
                <div class="row center">
                    <span style="opacity: .7; margin-top: 20px;" class="animated bounceInUp">
                        No family member account. <a href="add-family.php" style="text-decoration:underline; font-weight:700">Create</a> now.
                    </span>
                </div>
            <?php
            } else { // has family member
                // fetch wallet from other family members
                $sqlFamWallet = mysqli_prepare(
                    $conn,
                    "SELECT
                        w.wallet_id,
                        w.wallet_name,
                        w.wallet_bal,
                        u.user_name
                    FROM
                        wallet w
                        INNER JOIN user u ON w.user_id = u.user_id
                    WHERE
                        w.wallet_status = 1
                        AND user_status = 1
                        AND access_level = 2
                        AND family_id = ?;"
                );
                mysqli_stmt_bind_param($sqlFamWallet, 'i', $family_id);
            ?>
                <div class="row center">
                    <div class="family-wallet col-11 col-sm-10 col-md-9 col-lg-8 col-xl-7 animated bounceInUp animated delay-0-2s">
                        <div class="card--header">
                            Family's Wallet(s)
                            <button type="button" id="new-family-wallet" class="btn-eff btn-add">
                                <i class="fal fa-plus"></i>
                                New
                                <span class="clicked"></span>
                            </button>
                        </div>
                        <div class="card--content">
                            <?php
                            if (mysqli_stmt_execute($sqlFamWallet)) {
                                $resultFamWallet = mysqli_stmt_get_result($sqlFamWallet);
                                if (mysqli_num_rows($resultFamWallet)) {
                            ?>
                                    <div class="table-head row">
                                        <div class="col-3-5 head-cell">
                                            Wallet
                                        </div>
                                        <div class="col-3-5 head-cell wallet-user-head">
                                            User
                                        </div>
                                        <div class="col-4 head-cell">
                                            Balance (RM)
                                        </div>
                                    </div>
                                    <?php
                                    while ($rowFamWallet = mysqli_fetch_array($resultFamWallet)) {
                                    ?>
                                        <div class="wallet-item row">
                                            <div class="col-3-5 wallet-name">
                                                <?php echo $rowFamWallet['wallet_name']; ?>
                                            </div>
                                            <div class="col-3-5 wallet-user">
                                                <?php echo $rowFamWallet['user_name']; ?>
                                            </div>
                                            <div class="col-4 wallet-balance">
                                                <?php echo $rowFamWallet['wallet_bal']; ?>
                                            </div>
                                            <div class="col-1 show-detail">
                                                <i class="fas fa-caret-down"></i>
                                            </div>
                                        </div>
                                        <div class="wallet-detail" data-value="<?php echo $rowFamWallet['wallet_id'] ?>">
                                            <div class="detail-wrapper">
                                                <?php
                                                $sqlFamPlan = mysqli_prepare($conn, "SELECT * FROM plan WHERE wallet_id = ? ORDER BY plan_name ASC;");
                                                mysqli_stmt_bind_param($sqlFamPlan, 'i', $rowFamWallet['wallet_id']);
                                                if (mysqli_stmt_execute($sqlFamPlan)) {
                                                    $resultFamPlan = mysqli_stmt_get_result($sqlFamPlan);
                                                    if (mysqli_num_rows($resultFamPlan)) {
                                                        while ($rowFamPlan = mysqli_fetch_array($resultFamPlan)) {
                                                            $amountSpentFam = getAmountSpent($conn, $rowFamPlan['category_id'], $rowFamPlan['wallet_id'], $rowFamPlan['start_date'], $rowFamPlan['end_date']);

                                                            $percentFam = calcPercentSpent($amountSpentFam, $rowFamPlan['plan_amount']);
                                                ?>
                                                            <div class="plan-item btn-eff row" data-value="<?php echo $rowFamPlan['plan_id']; ?>">
                                                                <div class="plan-name col-7-5 col-sm-9 col-md-9 col-lg-9-5 col-xl-10">
                                                                    <?php echo $rowFamPlan['plan_name']; ?>
                                                                </div>
                                                                <div class="spent row col-4-5 col-sm-3 col-md-3 col-lg-2-5 col-xl-2">
                                                                    <div class="col-5">Spent:</div>
                                                                    <span class="col-7"><?php echo $percentFam . ' '; ?>%</span>
                                                                </div>
                                                                <div class="clicked"></div>
                                                            </div>
                                                        <?php
                                                        }
                                                    } else {
                                                        ?>
                                                        <div class="empty-plan">
                                                            This wallet doesn't have any budget plan.
                                                        </div>
                                                <?php
                                                    }
                                                }
                                                ?>
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
                                                <button type="button" class="btn-custom btn-transfer btn-eff">
                                                    <i class="fal fa-sync"></i>
                                                    <span>Transfer</span>
                                                    <span class="clicked"></span>
                                                </button>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    mysqli_stmt_close($sqlFamPlan);
                                } else {
                                    ?>
                                    <div class="wallet-item row" style="justify-content: center; font-weight: normal; font-size: 16px; opacity:.7; margin-top: 10px;">No wallet</div>
                            <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
        <?php
            }
        }
        ?>
    </section>

    <div class="modal fade animated bounceInUp fast auto-height" id="modal-add-wallet">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="reset" form="form-add-wallet" class="btn-modal-close btn-eff">
                        <i class="far fa-arrow-left"></i>
                        <span class="clicked"></span>
                    </button>
                    <h4 class="modal-title">New Wallet</h4>
                </div>
                <div class="modal-body">
                    <form method="POST" id="form-add-wallet" class="form-wallet" novalidate autocomplete="off">
                        <div class="row new-name">
                            <span class="input-title col-5-5">Wallet Name</span>
                            <input type="text" name="name" id="wallet-name" class="col-6-5 form-input" placeholder="Enter wallet name">
                        </div>
                        <div class="row new-balance">
                            <span class="input-title col-5-5">Initial Balance</span>
                            <div class="col-6-5 input-amount row" style="margin: 0">
                                <span class="col-2">RM</span>
                                <input type="text" name="initial_balance" id="initial-balance" class="col-9-5 offset-0-5 form-input input-number" placeholder="Enter amount" value="0">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-custom-footer">
                    <button type="reset" form="form-add-wallet" class="btn-modal-close btn-cancel btn-form btn-eff">
                        <i class="far fa-times"></i>
                        <span class="clicked"></span>
                    </button>
                    <button type="submit" form="form-add-wallet" id="save-wallet" class="btn-save btn-form btn-eff">
                        <i class="far fa-check"></i>
                        <span class="clicked"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade animated bounceIn fast auto-height" id="modal-edit-wallet">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="reset" form="form-edit-wallet" class="btn-modal-close btn-eff">
                        <i class="far fa-arrow-left"></i>
                        <span class="clicked"></span>
                    </button>
                    <h4 class="modal-title">Edit Wallet Name</h4>
                </div>
                <div class="modal-body">
                    <form method="POST" id="form-edit-wallet" class="form-wallet row" novalidate autocomplete="off">
                        <input type="text" name="wallet_name" id="wallet-edit-name" class="form-input col-10 offset-1" placeholder="Enter wallet name">
                        <input type="hidden" name="wallet_id" id="wallet-id">
                    </form>
                </div>
                <div class="modal-custom-footer">
                    <button type="reset" form="form-edit-wallet" class="btn-modal-close btn-cancel btn-form btn-eff">
                        <i class="far fa-times"></i>
                        <span class="clicked"></span>
                    </button>
                    <button type="submit" form="form-edit-wallet" class="btn-save btn-form btn-eff">
                        <i class="far fa-check"></i>
                        <span class="clicked"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php if ($family_id && $acc_lv == 1) { ?>
        <div class="modal fade animated bounceInUp fast auto-height" id="modal-add-fam-wallet">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="reset" form="form-add-fam-wallet" class="btn-modal-close btn-eff">
                            <i class="far fa-arrow-left"></i>
                            <span class="clicked"></span>
                        </button>
                        <h4 class="modal-title">New Wallet for Family Member</h4>
                    </div>
                    <div class="modal-body">
                        <form method="POST" id="form-add-fam-wallet" class="form-wallet" novalidate autocomplete="off">
                            <div class="row new-name">
                                <span class="input-title col-5-5">Wallet Name</span>
                                <input type="text" name="fam_wallet_name" id="fam-wallet-name" class="col-6-5 form-input" placeholder="Enter wallet name">
                            </div>
                            <div class="row new-balance">
                                <span class="input-title col-5-5">Initial Balance</span>
                                <div class="col-6-5 input-amount row" style="margin: 0">
                                    <span class="col-2">RM</span>
                                    <input type="text" name="fam_wallet_balance" id="fam-initial-balance" class="col-9-5 offset-0-5 form-input input-number" placeholder="Enter amount" value="0">
                                </div>
                            </div>
                            <div class="row new-user">
                                <div class="input-title col-5-5">User</div>
                                <select name="fam_wallet_user" id="fam-wallet-user" class="col-6-5 form-input">
                                    <?php
                                    $sqlFamUser = mysqli_prepare($conn, "SELECT user_id, user_name FROM user WHERE family_id = ? AND user_status = 1 AND access_level = 2;");
                                    mysqli_stmt_bind_param($sqlFamUser, 'i', $family_id);
                                    if (mysqli_stmt_execute($sqlFamUser)) {
                                        $resultFamUser = mysqli_stmt_get_result($sqlFamUser);
                                        if (mysqli_num_rows($resultFamUser)) {
                                    ?>
                                            <option disabled selected class="select-placeholder">- Select a user -</option>
                                            <?php
                                            while ($rowFamUser = mysqli_fetch_array($resultFamUser)) {

                                            ?>
                                                <option value="<?php echo $rowFamUser['user_id']; ?>"><?php echo $rowFamUser['user_name']; ?></option>
                                    <?php
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-custom-footer">
                        <button type="reset" form="form-add-fam-wallet" class="btn-modal-close btn-cancel btn-form btn-eff">
                            <i class="far fa-times"></i>
                            <span class="clicked"></span>
                        </button>
                        <button type="submit" form="form-add-fam-wallet" class="btn-save btn-form btn-eff">
                            <i class="far fa-check"></i>
                            <span class="clicked"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade animated bounceInUp fast auto-height" id="modal-transfer">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="reset" form="form-transfer" class="btn-modal-close btn-eff">
                            <i class="far fa-arrow-left"></i>
                            <span class="clicked"></span>
                        </button>
                        <h4 class="modal-title">Transfer Money</h4>
                    </div>
                    <div class="modal-body">
                        <form method="POST" id="form-transfer" class="form-wallet" novalidate autocomplete="off">
                            <div class="row transfer-mode">
                                <div class="col-5-5 input-title">Transfer Mode</div>
                                <div class="col-6-5 row" style="margin: 0">
                                    <div class="label">In</div>
                                    <div class="label col-4 col-sm-3 wrapper-switch">
                                        <input type="checkbox" id="switch" name="transfer_mode">
                                        <label for="switch" id="lbl-switch"></label>
                                    </div>
                                    <div class="label">Out</div>
                                </div>
                            </div>
                            <div class="row transfer-from">
                                <div class="col-5-5 input-title mode">From</div>
                                <select name="wallet_parent" id="wallet-parent" class="col-6-5 form-input">
                                    <option selected disabled class="select-placeholder">- Select a wallet -</option>
                                </select>
                            </div>
                            <div class="row transfer-amount">
                                <div class="col-5-5 input-title">Amount</div>
                                <div class="col-6-5 input-amount row" style="margin: 0">
                                    <span class="col-2">RM</span>
                                    <input type="text" name="transfer_amount" id="transfer-amount" class="col-9-5 offset-0-5 form-input input-number" placeholder="Enter amount" value="0">
                                </div>
                            </div>
                            <input type="hidden" name="wallet_child" id="wallet-child">
                        </form>
                    </div>
                    <div class="modal-custom-footer">
                        <button type="reset" form="form-transfer" class="btn-modal-close btn-cancel btn-form btn-eff">
                            <i class="far fa-times"></i>
                            <span class="clicked"></span>
                        </button>
                        <button type="submit" form="form-transfer" class="btn-save btn-form btn-eff">
                            <i class="far fa-check"></i>
                            <span class="clicked"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade auto-height modal-small" id="modal-insufficient">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Insufficient Balance</h4>
                    </div>
                    <div class="modal-body" style="padding-top: 10px">
                        <div style="margin-bottom:10px;">Unable to perform this action.</div>
                        <div>Insufficient amount of money in the wallet chosen.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" style="background-color:var(--colorMain); color:#fff;padding-top:.2rem;padding-bottom:.2rem;border:none" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <div class="modal fade modal-small" id="modal-delete-wallet" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Delete Wallet</h4>
                </div>
                <div class="modal-body">
                    <div style="margin-bottom:15px;">
                        <div style="margin-bottom: 8px;">Would you like to delete this wallet?</div>
                        All transactions and budget plans associated with this wallet will be removed as well.
                    </div>
                    <span style="font-weight:700">Note: </span>You can't undo this action.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-dismiss="modal">Cancel</button>
                    <button type="button" id="confirm-delete" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade animated bounceInUp fast" id="modal-add-budget">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="reset" form="form-add-budget" class="btn-modal-close btn-eff">
                        <i class="far fa-arrow-left"></i>
                        <span class="clicked"></span>
                    </button>
                    <h4 class="modal-title">New Budget Plan</h4>
                </div>
                <div class="modal-body">
                    <form method="POST" id="form-add-budget" class="form-wallet" novalidate autocomplete="off">
                        <div class="budget-name row">
                            <span class="input-title col-4-5">Plan Name</span>
                            <input type="text" name="budget_name" id="new-budg-name" class="col-7-5 form-input required" placeholder="Enter plan name">
                        </div>
                        <div class="budget-category row">
                            <span class="input-title col-4-5">Category</span>
                            <select name="category" id="category" class="col-7-5 form-input required">
                                <option disabled selected class="select-placeholder">- Select a category -</option>
                                <?php
                                $type = 'expenses';
                                $sqlCat = mysqli_prepare($conn, "SELECT category_id, category_name FROM category WHERE category_type = ? ORDER BY category_name ASC;");
                                mysqli_stmt_bind_param($sqlCat, 's', $type);
                                if (mysqli_stmt_execute($sqlCat)) {
                                    $resultCat = mysqli_stmt_get_result($sqlCat);
                                    if (!(mysqli_num_rows($resultCat) <= 0)) {
                                        while ($rowCat = mysqli_fetch_array($resultCat)) {
                                ?>
                                            <option value="<?php echo $rowCat['category_id']; ?>">
                                                <?php echo ucfirst($rowCat['category_name']); ?>
                                            </option>
                                <?php
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="budget-amount row">
                            <span class="input-title col-4-5">Amount</span>
                            <div class="col-7-5 input-amount row" style="margin: 0">
                                <span class="col-2">RM</span>
                                <input type="text" name="amount" id="amount" class="form-input col-10 required input-number" placeholder="Enter amount">
                            </div>
                        </div>
                        <div class="budget-start row">
                            <span class="input-title col-4-5">Start from</span>
                            <input type="date" name="start_date" id="start-date" class="col-7-5 form-input input-date required">
                        </div>
                        <div class="budget-end row">
                            <div class="col-1 info" data-toggle="tooltip" title="End date of the plan. If it is not specified, the plan will be calculated for the next 30 days, else it will be calculated from the start date until the end date." data-placement="bottom">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div class="col-1" style="display:flex; align-items:center; justify-content:center;">
                                <input type="checkbox" id="chk-end-date">
                            </div>
                            <label for="chk-end-date" class="input-title col-2-5">Until</label>
                            <input type="date" name="end_date" id="end-date" class="col-7-5 form-input input-date" disabled>
                        </div>
                        <div class="budget-alert row">
                            <div class="col-1 info" data-toggle="tooltip" title="If plan alert is enabled, the user will be informed when his expenses on the category reach the specified amount (in %) of the amount set." data-placement="bottom">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div class="col-1" style="display:flex; align-items:center; justify-content:center;">
                                <input type="checkbox" id="chk-alert">
                            </div>
                            <label for="chk-alert" class="input-title col-2-5">Alert</label>
                            <input type="text" name="alert" id="alert" class="col-2-5 form-input input-number" disabled>
                            <span class="col" style="display: flex; align-items:center; padding-left:5px;transition:opacity .2s">%</span>
                        </div>
                        <input type="hidden" name="wallet_id" id="wallet-id-budget">
                    </form>
                </div>
                <div class="modal-custom-footer">
                    <button type="reset" form="form-add-budget" class="btn-modal-close btn-cancel btn-form btn-eff">
                        <i class="far fa-times"></i>
                        <span class="clicked"></span>
                    </button>
                    <button type="submit" form="form-add-budget" class="btn-save btn-form btn-eff">
                        <i class="far fa-check"></i>
                        <span class="clicked"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade modal-small" id="modal-budget-existed" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <i class="fal fa-exclamation-circle" style="color:red;font-size:24px;margin-right:10px"></i>
                    <h4 class="modal-title">Duplicated Budget Plan</h4>
                </div>
                <div class="modal-body">
                    A budget plan with the same <strong>name</strong>, <strong>wallet</strong>, <strong>category</strong>, <strong>start</strong> and <strong>end date</strong> already exists.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" style="background-color: var(--colorMain);color:#fff;" data-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php if ($acc_lv == 1) { ?>
        <div class="modal fade animated fadeInRight fast" id="modal-edit-budget" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="btn-modal-close btn-eff">
                            <i class="far fa-arrow-left"></i>
                            <span class="clicked"></span>
                        </button>
                        <h4 class="modal-title">Edit Budget Plan</h4>
                    </div>
                    <div class="modal-body">
                        <form method="POST" id="form-edit-budget" class="form-wallet" novalidate autocomplete="off">
                            <div class="edit-name row">
                                <span class="input-title col-4-5">Plan Name</span>
                                <input type="text" name="ebudget_name" id="ebudg-name" class="col-7-5 form-input required" placeholder="Enter plan name">
                            </div>
                            <div class="edit-category row">
                                <span class="input-title col-4-5">Category</span>
                                <select name="ebudget_category" id="ebudg-categ" class="col-7-5 form-input required"></select>
                            </div>
                            <div class="edit-amount row">
                                <span class="input-title col-4-5">Amount</span>
                                <div class="col-7-5 input-amount row" style="margin: 0">
                                    <span class="col-2">RM</span>
                                    <input type="text" name="ebudget_amount" id="ebudg-amount" class="form-input col-10 required input-number" placeholder="Enter amount">
                                </div>
                            </div>
                            <div class="edit-start row">
                                <span class="input-title col-4-5">Start from</span>
                                <input type="date" name="ebudget_start" id="ebudg-start" class="col-7-5 form-input input-date required">
                            </div>
                            <div class="edit-end row">
                                <div class="col-1 info" data-toggle="tooltip" title="End date of the plan. If it is not specified, the plan will be calculated for the next 30 days, else it will be calculated from the start date until the end date." data-placement="bottom">
                                    <i class="fas fa-question-circle"></i>
                                </div>
                                <div class="col-1" style="display:flex; align-items:center; justify-content:center;">
                                    <input type="checkbox" id="edit-chk-end-date">
                                </div>
                                <label for="edit-chk-end-date" class="input-title col-2-5">Until</label>
                                <input type="date" name="ebudget_end" id="ebudg-end" class="col-7-5 form-input input-date" disabled>
                            </div>
                            <div class="edit-alert row">
                                <div class="col-1 info" data-toggle="tooltip" title="If plan alert is enabled, the user will be informed when his expenses on the category reach the specified amount (in %) of the amount set." data-placement="bottom">
                                    <i class="fas fa-question-circle"></i>
                                </div>
                                <div class="col-1" style="display:flex; align-items:center; justify-content:center;">
                                    <input type="checkbox" id="edit-chk-alert">
                                </div>
                                <label for="edit-chk-alert" class="input-title col-2-5">Alert</label>
                                <input type="text" name="ebudget_alert" id="ebudg-alert" class="col-2-5 form-input input-number" disabled>
                                <span class="col" style="display: flex; align-items:center; padding-left:5px;transition:opacity .2s">%</span>
                            </div>
                            <input type="hidden" name="ebudget_wallet_id" id="ebudg-wallet">
                            <input type="hidden" name="ebudget_plan_id" id="ebudg-plan">
                        </form>
                    </div>
                    <div class="modal-custom-footer">
                        <button type="reset" form="form-edit-budget" class="btn-modal-close btn-cancel btn-form btn-eff">
                            <i class="far fa-times"></i>
                            <span class="clicked"></span>
                        </button>
                        <button type="submit" form="form-edit-budget" class="btn-save btn-form btn-eff">
                            <i class="far fa-check"></i>
                            <span class="clicked"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade modal-small" id="modal-delete-budget" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Delete Budget Plan</h4>
                    </div>
                    <div class="modal-body">
                        <div style="margin-bottom:15px;">Would you like to delete this budget plan?</div>
                        <strong>Note: </strong>You can't undo this action.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn" data-dismiss="modal">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-danger" id="confirm-delete-budget">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</body>

</html>