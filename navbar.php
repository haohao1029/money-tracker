<?php refreshNoti($conn, $user_id); ?>

<nav id="navbar">
    <a href="transaction.php" class="brand-link">
        <img src="images/brand.png" alt="SaveTrack" class="logo">
        <div class="brand-name">SaveTrack</div>
    </a>

    <div class="tab-wrapper">
        <a href="transaction.php">
            <div class="tab<?php echo $fileName == 'transaction' ? ' tab-active' : ''; ?>">Transaction</div>
        </a>
        <a href="wallet.php">
            <div class="tab<?php echo $fileName == 'wallet' ? ' tab-active' : ''; ?>">Wallet</div>
        </a>
        <a href="summary.php">
            <div class="tab<?php echo $fileName == 'summary' ? ' tab-active' : ''; ?>">Summary</div>
        </a>
    </div>

    <div class="user-wrapper">
        <div class="notification">
            <i class="fas fa-bell<?php echo count($_SESSION['alert']) ? '' : ' no-alert'; ?>" data-after="<?php echo count($_SESSION['alert']); ?>"></i>
            <div class="clicked sm"></div>
        </div>
        <div id="noti-panel">
            <div class="noti-header">Notifications</div>
            <div class="noti-body container-fluid" style="padding: 0">
                <?php
                // array_splice($_SESSION['alert'], 0); /// this remove all alert from the array
                $countAlert = count($_SESSION['alert']);
                $arrAlert = array();
                if ($countAlert) {
                    foreach ($_SESSION['alert'] as $id => $percent) {
                        $sqlNoti = "SELECT p.plan_name, w.wallet_name FROM plan p INNER JOIN wallet w ON p.wallet_id = w.wallet_id WHERE p.plan_id = $id;";
                        $resultNoti = mysqli_query($conn, $sqlNoti);
                        $row = mysqli_fetch_array($resultNoti);
                        $planName = $row['plan_name'];
                        $walletName = $row['wallet_name'];
                ?>
                        <div class="noti-item" data-value="<?php echo $id; ?>">
                            <div class="percent-wrapper row">
                                <span class="col-3" style="padding:0;font-weight:700">Alert</span><span style="color: #f00;margin-right: 6px"><?php echo $percent; ?>%</span> of budget spent.
                            </div>
                            <div class="plan-wrapper row">
                                <span class="col-3" style="padding:0;font-weight:700">Plan</span><?php echo $planName; ?>
                            </div>
                            <div class="wallet-wrapper row">
                                <span class="col-3" style="padding:0;font-weight:700">Wallet</span><?php echo $walletName; ?>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo '<div class="noti-empty">You have no notifications.</div>';
                }
                ?>
            </div>
        </div>
        <div class="user">
            <a href="account.php">
                <div class="user-name">
                    <i class="fad fa-user-circle"></i>
                    <span><?php echo $username; ?></span>
                </div>
            </a>
            <div class="user-option">
                <div class="option-toggle">
                    <i class="fas fa-caret-down"></i>
                    <div class="clicked sm"></div>
                </div>
                <div class="option-wrapper">
                    <a href="account.php">
                        <div class="option">
                            <i class="fad fa-user-cog"></i>
                            My Account
                        </div>
                    </a>
                    <?php if ($_SESSION['access_lv'] == 1) { ?>
                        <a href="family.php">
                            <div class="option">
                                <i class="fad fa-users-cog"></i>
                                My Family
                            </div>
                        </a>
                    <?php } ?>
                    <a href="logout.php">
                        <div class="option">
                            <i class="fad fa-sign-out"></i>
                            Logout
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<div id="side-nav">
    <button class="side-nav-toggle hamburger hamburger--slider-r" type="button">
        <span class="hamburger-box">
            <span class="hamburger-inner"></span>
        </span>
        <div class="clicked sm"></div>
    </button>
    <div class="side-brand">
        <img src="images/brand.png" alt="budgettrackingsystem" class="logo">
        <div class="brand-name">SaveTrack</div>
    </div>

    <div class="side-header">
        <div class="name">
            <i class="fad fa-user-circle"></i>
            <?php echo $username; ?>
        </div>
    </div>

    <div class="navigation">
        <a href="transaction.php">
            <div class="nav-button<?php echo $fileName == 'transaction' ? ' nav-active' : ''; ?>">
                <i class="fad fa-exchange-alt"></i>
                Transaction
            </div>
        </a>
        <a href="wallet.php">
            <div class="nav-button<?php echo $fileName == 'wallet' ? ' nav-active' : ''; ?>">
                <i class="fad fa-wallet"></i>
                Wallet
            </div>
        </a>
        <a href="summary.php">
            <div class="nav-button<?php echo $fileName == 'summary' ? ' nav-active' : ''; ?>">
                <i class="fad fa-chart-pie"></i>
                Summary
            </div>
        </a>
    </div>
    <hr>
    <div class="account-option">
        <a href="account.php">
            <div class="nav-button<?php echo $fileName == 'account' ? ' nav-active' : ''; ?>">
                <i class="fad fa-user-cog"></i>
                My Account
            </div>
        </a>
        <?php if ($_SESSION['access_lv'] == 1) { ?>
            <a href="family.php">
                <div class="nav-button<?php echo $fileName == 'family' ? ' nav-active' : ''; ?>">
                    <i class="fad fa-users-cog"></i>
                    My Family
                </div>
            </a>
        <?php } ?>
    </div>
    <hr>
    <div class="logout">
        <a href="logout.php">
            <div class="nav-button">
                <i class="fad fa-sign-out"></i>
                Logout
            </div>
        </a>
    </div>
</div>

<div class="modal fade animated bounceIn fast" id="modal-view-budget">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-modal-close btn-eff">
                    <i class="far fa-arrow-left"></i>
                    <span class="clicked"></span>
                </button>
                <h4 class="modal-title">Budget Plan Details</h4>
                <?php if ($acc_lv == 1 && $fileName == 'wallet') { ?>
                    <button type="button" id="btn-delete-budget">
                        <i class="fas fa-trash"></i>
                    </button>
                <?php } ?>
            </div>
            <div class="modal-body">
                <table class="tbl-budget">
                    <tr>
                        <td>Name</td>
                        <td class="detail-name"></td>
                    </tr>
                    <tr>
                        <td>Category</td>
                        <td class="detail-categ"></td>
                    </tr>
                    <tr>
                        <td>Wallet</td>
                        <td class="detail-wallet"></td>
                    </tr>
                    <tr>
                        <td>Amount (RM)</td>
                        <td class="detail-amount"></td>
                    </tr>
                    <tr>
                        <td>Alert</td>
                        <td class="detail-alert"></td>
                    </tr>
                    <tr>
                        <td>Spent (RM)</td>
                        <td class="detail-spent"></td>
                    </tr>
                    <tr style="height: 65px">
                        <td colspan="2" id="spentBar" style="position:relative;height:20px;"></td>
                    </tr>
                    <tr>
                        <td>Start from</td>
                        <td class="detail-start"></td>
                    </tr>
                    <tr>
                        <td>Until</td>
                        <td class="detail-end"></td>
                    </tr>
                </table>
            </div>
            <?php if ($acc_lv == 1) { ?>
                <div class="modal-custom-footer" style="justify-content: flex-end">
                    <?php if ($fileName == 'wallet') { ?>
                        <button type="button" id="btn-edit-budget" class="btn-custom btn-form btn-eff" style="background-color: var(--colorMain)">
                            <i class="fas fa-pen"></i>
                            <span class="clicked"></span>
                        </button>
                    <?php } else {
                        echo '<div class="proc-wallet">Proceed to <a href="wallet.php" style="text-decoration:underline;font-weight:700">Wallet</a> section to make changes.</div>';
                    } ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>