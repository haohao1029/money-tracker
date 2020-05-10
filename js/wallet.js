$(function () {
    $(document).on('click', '.btn-eff', function () {
        clickEffect($(this), $(this).outerWidth() + 5);
    });

    $('[data-toggle="tooltip"]').tooltip();

    const modalNewWallet = $('#modal-add-wallet');
    const modalNewFamWallet = $('#modal-add-fam-wallet');
    const allNewModal = modalNewWallet.add(modalNewFamWallet);

    const modalEditWallet = $('#modal-edit-wallet');
    const modalEditFamWallet = $('#modal-edit-fam-wallet');
    const allEditModal = modalEditWallet.add(modalEditFamWallet);

    const modalNewBudget = $('#modal-add-budget');
    const modalViewBudget = $('#modal-view-budget');
    const modalEditBudget = $('#modal-edit-budget');

    const modalTransfer = $('#modal-transfer');

    const modalBounceInUp = allNewModal.add(modalNewBudget).add(modalTransfer);
    const modalBounceIn = allEditModal;

    // new personal wallet
    $('#new-wallet').on('click', function () {
        modalNewWallet.modal();
    });

    // new family wallet
    $('#new-family-wallet').on('click', function () {
        modalNewFamWallet.modal();
    });

    $('#btn-delete-budget').on('click', function () {
        $('#modal-delete-budget').modal();
    });

    $('#btn-edit-budget').on('click', function () {
        hideModal(modalViewBudget);
        // fetch category option
        $.ajax({
            type: "POST",
            url: "ajax/fetch-category.php",
            data: {
                'trans_type': 'expenses'
            },
            success: function (response) {
                if (response != 'error') {
                    $('#ebudg-categ').html(response);
                }
            }
        });

        const planID = $(this).val();
        $.ajax({
            type: "POST",
            url: "ajax/budget-detail.php",
            data: {
                'plan_id': planID
            },
            success: function (response) {
                response = JSON.parse(response);
                const name = response['plan_name'];
                const catID = response['cat_id'];
                const amount = response['amount'];
                let start = response['start'];
                let end = response['end'];
                const alert = response['alert'];
                const walletID = response['wall_id'];

                // fill into form
                $('#ebudg-name').val(name);
                $('#ebudg-categ option[value="' + catID + '"]').prop('selected', true);
                $('#ebudg-amount').val(amount);

                // start date
                start = start.match(/[0-9]{2}[\-][0-9]{2}[\-][0-9]{4}/);
                const [dd, mm, yyyy] = start[0].split('-');
                const val_start = `${yyyy}-${mm}-${dd}`;
                $('#ebudg-start').val(val_start);

                // end date
                if (end) {
                    end = end.match(/[0-9]{2}[\-][0-9]{2}[\-][0-9]{4}/);
                    const [dd, mm, yyyy] = end[0].split('-');
                    const val_end = `${yyyy}-${mm}-${dd}`;
                    $('#ebudg-end').val(val_end).prop('disabled', false);
                    $('#edit-chk-end-date').prop('checked', true);
                }

                // alert
                if (alert) {
                    $('#ebudg-alert').val(alert).prop('disabled', false);
                    $('#edit-chk-alert').prop('checked', true);
                }

                $('#ebudg-wallet').val(walletID);
                $('#ebudg-plan').val(planID);
            }
        });

        setTimeout(() => {
            modalEditBudget.modal();
        }, 550);
    });

    // modal - bounceInUp
    modalBounceInUp.on('show.bs.modal', function () {
        const modal = $(this);
        modal.find('.btn-modal-close').one('click', function () {
            hideModal(modal);
        });
    }).on('shown.bs.modal', function () {
        $('.modal-backdrop').addClass('custom-transition');
    }).on('hidden.bs.modal', function () {
        const modal = $(this);
        modal.removeClass('bounceOutLeft').addClass('bounceInUp').off('animationend');
        modal.find('.btn-modal-close').off('click');
        $('.modal-backdrop').removeClass('custom-transition');
    });

    // modal - bounceIn
    modalBounceIn.on('show.bs.modal', function () {
        const modal = $(this);
        modal.find('.btn-modal-close').one('click', function () {
            hideModal(modal);
        });
    }).on('shown.bs.modal', function () {
        $('.modal-backdrop').addClass('custom-transition');
    }).on('hidden.bs.modal', function () {
        const modal = $(this);
        modal.removeClass('bounceOutLeft').addClass('bounceIn').off('animationend');
        modal.find('.btn-modal-close').off('click');
        $('.modal-backdrop').removeClass('custom-transition');
    });

    // modal - edit budget (fadeInRight)
    modalEditBudget.on('show.bs.modal', function () {
        const modal = $(this);
        modal.find('.btn-modal-close').one('click', function () {
            hideModal(modal);
        });
    }).on('shown.bs.modal', function () {
        $('.modal-backdrop').addClass('custom-transition');
    }).on('hidden.bs.modal', function () {
        const modal = $(this);
        modal.removeClass('bounceOutLeft').addClass('fadeInRight').off('animationend');
        modal.find('.btn-modal-close').off('click');
        $('.modal-backdrop').removeClass('custom-transition');
        $('#form-edit-budget').trigger('reset');
    });

    /**
     * wallet detail section
     * including 3 buttons who trigger the modal for delete / edit wallet 
     * + modal for add budget plan
     * + modal for transferring
     */
    $('.wallet-detail').hide();
    $('.card--content').on('click', '.wallet-item', function () {
        $(this).next('.wallet-detail').slideToggle(300).siblings('.wallet-detail').slideUp(300);
        $(this).find('i').toggleClass('active');
        $(this).siblings('.wallet-item').find('i').removeClass('active');
    }).on('click', '.btn-delete', function () {
        // delete wallet // share
        const id = $(this).parents('.wallet-detail').attr('data-value');
        $('#modal-delete-wallet').modal().find('#confirm-delete').val(id);
    }).on('click', '.btn-edit', function () {
        // edit wallet // share
        const id = $(this).parents('.wallet-detail').attr('data-value');
        console.log(id);
        $('#wallet-id').val(id);
        $.ajax({
            type: "POST",
            url: "ajax/fetch-wallet-name.php",
            data: {
                'wallet_id': id
            },
            success: function (response) {
                console.log(response);
                if (response != 'fail') {
                    $('#wallet-edit-name').val(decodeHtmlEntity(response));
                }
            }
        });
        $('#modal-edit-wallet').modal();
    }).on('click', '.btn-add-plan', function () {
        // add plan // share
        const id = $(this).parents('.wallet-detail').attr('data-value');
        $('#wallet-id-budget').val(id);
        $('#modal-add-budget').modal();
    }).on('click', '.btn-transfer', function () {
        // transfer
        const id = $(this).parents('.wallet-detail').attr('data-value');
        $('#wallet-child').val(id);
        // fetch wallet
        $.ajax({
            type: "POST",
            url: "ajax/fetch-wallet.php",
            success: function (response) {
                if (response != 'error') {
                    const placeholder = $('#wallet-parent .select-placeholder').detach();
                    $('#wallet-parent').html(response).prepend(placeholder);
                    $('#wallet-parent .select-placeholder').prop('selected', true);
                }
            }
        });
        $('#modal-transfer').modal();
    });

    // transfer mode switch
    $('#switch').on('change', function () {
        if ($(this).prop('checked')) {
            $('.mode').html('To');
        } else {
            $('.mode').html('From');
        }
    });

    $('.input-number').inputFilter(function (value) {
        return /^-?\d*[.,]?\d{0,2}$/.test(value);
    });

    // submit transfer
    $('#form-transfer').on({
        'submit': function (e) {
            e.preventDefault();
            const checkResult = $('#form-transfer .form-input').validateEmptyInput();
            const inputEmpty = $(checkResult).map(function () { return this.toArray() });
            $('.error').remove();
            if ($('#transfer-amount').val().valueOf() == 0) {
                $('#transfer-amount').after('<div class="error offset-5-5">Amount can\'t be zero.</div>');
            };
            if (checkResult) {
                inputEmpty.after('<div class="error offset-5-5">This field is required.</div>');
            } else {
                const form = $('#form-transfer')[0];
                const transferData = new FormData(form);
                $.ajax({
                    type: "POST",
                    url: "ajax/transfer.php",
                    data: transferData,
                    processData: false,
                    cache: false,
                    contentType: false,
                    success: function (response) {
                        if (response == 'insufficient') {
                            $('#modal-insufficient').modal();
                        } else {
                            console.log(response);
                            response = JSON.parse(response);
                            const status = response['status'];
                            const childID = response['child_id'];
                            const child = response['child_wallet'];
                            const parentID = response['parent_id'];
                            const parent = response['parent_wallet'];

                            console.log(status);
                            if (status == 'success') {
                                const childBalance = $('.wallet-detail[data-value="' + childID + '"]').prev('.wallet-item').find('.wallet-balance');
                                const parentBalance = $('.wallet-detail[data-value="' + parentID + '"]').prev('.wallet-item').find('.wallet-balance');
                                const both = childBalance.add(parentBalance);

                                both.animate({ opacity: 0 }, 200);
                                setTimeout(() => {
                                    childBalance.html(child);
                                    parentBalance.html(parent);
                                    both.animate({ opacity: 1 }, 200);
                                }, 200);

                                $('#form-transfer').trigger('reset');
                                hideModal($('#modal-transfer'));
                            } else {
                                alert('Oops! Some error(s) occur. Please try again.');
                                location.reload();
                                return;
                            }
                        }
                    }
                });
            }
        }, 'reset': function () {
            $('.error').remove();
        }
    });

    // submit delete action
    $('#confirm-delete').on('click', function () {
        const id = $(this).val();
        $.ajax({
            type: "POST",
            url: "ajax/delete-wallet.php",
            data: {
                'wallet_id': id
            },
            success: function (response) {
                response = JSON.parse(response);
                const status = response['status'];
                const alertCount = response['alert_count'];
                const notiEl = response['noti_el'];
                if (status == 'success') {
                    $('#modal-delete-wallet').modal('hide');
                    const walletDetail = $('.wallet-detail[data-value="' + id + '"]');
                    const walletItem = walletDetail.prev('.wallet-item');
                    const deletedWallet = walletDetail.add(walletItem);
                    deletedWallet.animate(
                        { opacity: 0 },
                        { queue: false, duration: 300 }
                    ).animate(
                        { height: 0 },
                        600
                    );
                    setTimeout(() => {
                        deletedWallet.remove();
                        if (!($('.user-wallet .wallet-item').length)) {
                            $('.user-wallet .table-head').remove();
                            $('.user-wallet .card--content').html(walletEmptyEl);
                        }
                        if (!($('.family-wallet .wallet-item').length)) {
                            $('.family-wallet .table-head').remove();
                            $('.family-wallet .card--content').html(walletEmptyEl);
                        }
                    }, 310);
                    $('.notification i').attr('data-after', alertCount);
                    if (alertCount) {
                        $('.notification i').removeClass('no-alert');
                    } else {
                        $('.notification i').addClass('no-alert');
                    }
                    $('.noti-body').html(notiEl);
                } else {
                    alert('Oops! Some error(s) occur. Please try again.');
                    location.reload();
                    return;
                }
            }
        });
    });

    // submit new wallet (personal)
    $('#form-add-wallet').on({
        'submit': function (e) {
            e.preventDefault();
            submitNewWallet();
        }, 'reset': function () {
            $('.error').remove();
        }
    });

    // submit new wallet (family)
    $('#form-add-fam-wallet').on({
        'submit': function (e) {
            e.preventDefault();
            submitNewWalletFam();
        }, 'reset': function () {
            $('.error').remove();
        }
    });

    // submit edit wallet name
    $('#form-edit-wallet').on({
        'submit': function (e) {
            e.preventDefault();
            const id = $(this).find('#wallet-id').val();
            const checkResult = $('#form-edit-wallet .form-input').validateEmptyInput();
            const inputEmpty = $(checkResult).map(function () { return this.toArray() });
            $('.error').remove();
            if (checkResult) {
                inputEmpty.after('<div class="error offset-1">Wallet name cannot be empty.</div>');
            } else {
                const form = $('#form-edit-wallet')[0];
                const walletData = new FormData(form);
                $.ajax({
                    type: "POST",
                    url: "ajax/edit-wallet-name.php",
                    data: walletData,
                    processData: false,
                    cache: false,
                    contentType: false,
                    success: function (response) {
                        if (response != 'fail') {
                            const name = $('.wallet-detail[data-value="' + id + '"]').prev('.wallet-item').find('.wallet-name');
                            name.animate({ opacity: 0 }, 200);
                            setTimeout(() => {
                                name.html(response).animate({ opacity: 1 }, 200);
                            }, 200);
                            $('#form-edit-wallet').trigger('reset');
                            hideModal($('#modal-edit-wallet'));
                        } else {
                            alert('Oops! Some error(s) occur. Please try again.');
                            location.reload();
                            return;
                        }
                    }
                });
            }
        }, 'reset': function () {
            $('.error').remove();
        }
    });

    // form - add plan
    $('#chk-end-date').on('change', function () {
        if ($(this)[0].checked) {
            $('#end-date').prop('disabled', false);
        } else {
            $('#end-date').prop('disabled', true);
        }
    });

    $('#chk-alert').on('change', function () {
        if ($(this)[0].checked) {
            $('#alert').prop('disabled', false);
        } else {
            $('#alert').prop('disabled', true);
        }
    });

    // form - edit plan
    $('#edit-chk-end-date').on('change', function () {
        if ($(this)[0].checked) {
            $('#ebudg-end').prop('disabled', false);
        } else {
            $('#ebudg-end').prop('disabled', true);
        }
    });

    $('#edit-chk-alert').on('change', function () {
        if ($(this)[0].checked) {
            $('#ebudg-alert').prop('disabled', false);
        } else {
            $('#ebudg-alert').prop('disabled', true);
        }
    });

    $('#modal-budget-existed').on('show.bs.modal', function () {
        var zIndex = parseInt($(this).css('z-index'));
        setTimeout(() => {
            $(this).css('z-index', zIndex + 10);
            setTimeout(function () {
                $('.modal-stack:last').css('z-index', zIndex + 9).addClass('modal-stack');
            }, 0);
        }, 10);
    });

    // submit new plan
    $('#form-add-budget').on({
        'submit': function (e) {
            e.preventDefault();
            let mustFill = $('#form-add-budget .form-input.required');
            if ($('#chk-end-date')[0].checked) {
                mustFill = mustFill.add('#end-date');
            }
            if ($('#chk-alert')[0].checked) {
                mustFill = mustFill.add('#alert');
            }
            const checkResult = mustFill.validateEmptyInput();
            const inputEmpty = $(checkResult).map(function () { return this.toArray() });
            $('.error').remove();
            if (checkResult) {
                inputEmpty.after('<div class="error offset-4-5">This field is required.</div>');
            } else {
                const form = $('#form-add-budget')[0];
                const budgetData = new FormData(form);
                $.ajax({
                    type: "POST",
                    url: "ajax/add-budget.php",
                    data: budgetData,
                    processData: false,
                    cache: false,
                    contentType: false,
                    success: function (response) {
                        if (response == 'existed') {
                            $('#modal-budget-existed').modal();
                        } else {
                            response = JSON.parse(response);
                            const status = response['status'];
                            const alertCount = response['alert_count'];
                            const planItem = response['plan_item'];
                            const notiEl = response['noti_el'];

                            if (status == 'success') {
                                // insert new plan item into body
                                const wallet_id = $('#wallet-id-budget').val();
                                const budgetWrapper = $('.wallet-detail[data-value="' + wallet_id + '"] .detail-wrapper');
                                if (!(budgetWrapper.find('.plan-item').length)) {
                                    budgetWrapper.find('.empty-plan').remove();
                                }
                                budgetWrapper.prepend(planItem);
                                const newPlanEl = budgetWrapper.find('.plan-item').eq(0);
                                newPlanEl.slideUp(0);
                                setTimeout(() => {
                                    newPlanEl.slideDown();
                                }, 400);
                            } else {
                                alert('Oops! Some error(s) occur. Please try again.');
                                location.reload();
                                return;
                            }

                            // alert
                            $('.notification i').attr('data-after', alertCount);
                            if (alertCount) {
                                $('.notification i').removeClass('no-alert');
                            } else {
                                $('.notification i').addClass('no-alert');
                            }

                            $('.noti-body').html(notiEl);

                            $('#form-add-budget').trigger('reset');
                            hideModal($('#modal-add-budget'));
                        }
                    }
                });
            }
        }, 'reset': function () {
            $('.error').remove();
            $('#end-date, #alert').prop('disabled', true);
            $('#category .select-placeholder').prop('selected', true);
        }
    });

    // submit delete plan form
    $('#confirm-delete-budget').on('click', function () {
        const id = $('#btn-delete-budget').val();
        $.ajax({
            type: "POST",
            url: "ajax/delete-budget.php",
            data: {
                'plan_id': id
            },
            success: function (response) {
                response = JSON.parse(response);
                const status = response['status'];
                const alertCount = response['alert_count'];
                const notiEl = response['noti_el'];

                if (status == 'success') {
                    $('#modal-delete-budget').modal('hide');
                    hideModal(modalViewBudget);
                    const toRemove = $('.plan-item[data-value="' + id + '"]');
                    const detailWrapper = toRemove.parent();
                    toRemove.animate(
                        { opacity: 0 },
                        { queue: false, duration: 300 }
                    ).animate(
                        { height: 0 },
                        600
                    );
                    setTimeout(() => {
                        toRemove.remove();
                        if (!(detailWrapper.find('.plan-item').length)) {
                            detailWrapper.html(planEmptyEl);
                        }
                    }, 310);

                    $('.notification i').attr('data-after', alertCount)
                    if (alertCount) {
                        $('.notification i').removeClass('no-alert');
                    } else {
                        $('.notification i').addClass('no-alert');
                    }
                    $('.noti-body').html(notiEl);
                } else {
                    alert('Oops! Some error(s) occur. Please try again.');
                    location.reload();
                    return;
                }
            }
        });
    });

    $('#form-edit-budget').on({
        'submit': function (e) {
            e.preventDefault();
            const id = $('#ebudg-plan').val();
            let mustFill = $('#form-edit-budget .form-input.required');
            if ($('#edit-chk-end-date')[0].checked) {
                mustFill = mustFill.add('#ebudg-end');
            }
            if ($('#edit-chk-alert')[0].checked) {
                mustFill = mustFill.add('#ebudg-alert');
            }
            const checkResult = mustFill.validateEmptyInput();
            const inputEmpty = $(checkResult).map(function () { return this.toArray() });
            $('.error').remove();
            if (checkResult) {
                inputEmpty.after('<div class="error offset-4-5">This field is required.</div>');
            } else {
                const form = $('#form-edit-budget')[0];
                const budgetData = new FormData(form);
                $.ajax({
                    type: "POST",
                    url: "ajax/edit-budget.php",
                    data: budgetData,
                    processData: false,
                    cache: false,
                    contentType: false,
                    success: function (response) {
                        if (response == 'existed') {
                            $('#modal-budget-existed').modal();
                        } else {
                            response = JSON.parse(response);
                            const status = response['status'];
                            const alertCount = response['alert_count'];
                            const newName = response['new_name'];
                            const newPercent = response['new_percent'];
                            const notiEl = response['noti_el'];

                            if (status == 'success') {
                                // change current name to the new one
                                const name = $('.plan-item[data-value="' + id + '"] .plan-name');
                                name.animate({ opacity: 0 }, 200);
                                setTimeout(() => {
                                    name.html(newName).animate({ opacity: 1 }, 200);
                                }, 200);
                                // change current percentage to the new one
                                const percentage = $('.plan-item[data-value="' + id + '"] span.col-7');
                                percentage.animate({ opacity: 0 }, 200);
                                setTimeout(() => {
                                    percentage.html(newPercent + ' %').animate({ opacity: 1 }, 200);
                                }, 200);
                            } else {
                                alert('Oops! Some error(s) occur. Please try again.');
                                location.reload();
                                return;
                            }

                            // alert
                            $('.notification i').attr('data-after', alertCount);
                            if (alertCount) {
                                $('.notification i').removeClass('no-alert');
                            } else {
                                $('.notification i').addClass('no-alert');
                            }

                            $('.noti-body').html(notiEl);

                            $('#form-edit-budget').trigger('reset');
                            hideModal($('#modal-edit-budget'));
                        }
                    }
                });
            }
        }, 'reset': function () {
            $('#ebudg-end, #ebudg-alert').prop('disabled', true);
        }
    });
});

const tableHeadPersonal = '<div class="table-head row"><div class="col-6 head-cell">Wallet</div><div class="col-5 head-cell">Balance (RM)</div></div>';
const tableHeadFamily = '<div class="table-head row"><div class="col-3-5 head-cell">Wallet</div><div class="col-3-5 head-cell wallet-user-head">User</div><div class="col-4 head-cell">Balance (RM)</div></div>';
const walletEmptyEl = '<div class="wallet-item row" style="justify-content: center; font-weight: normal;font-size:16px;opacity:.7;margin-top:10px;">No wallet</div>';
const planEmptyEl = '<div class="empty-plan">This wallet doesn\'t have any budget plan.</div >';

// for personal new wallet
function submitNewWallet() {
    const checkResult = $('#form-add-wallet .form-input').validateEmptyInput();
    const inputEmpty = $(checkResult).map(function () { return this.toArray() });
    $('.error').remove();
    if (checkResult) {
        inputEmpty.after('<div class="error offset-5-5">This field is required.</div>');
    } else {
        const form = $('#form-add-wallet')[0];
        const walletData = new FormData(form);
        $.ajax({
            type: "POST",
            url: "ajax/add-wallet.php",
            data: walletData,
            processData: false,
            cache: false,
            contentType: false,
            success: function (response) {
                if (!(response == 'fail' || response == 'empty')) {
                    const count = $('.user-wallet .card--content>*').length;
                    if (count == 1) {
                        $('.user-wallet .card--content').html(tableHeadPersonal);
                    }
                    $('.user-wallet .table-head').after(response);
                    const newWallet = $('.user-wallet .wallet-item').eq(0);
                    const newDetail = $('.user-wallet .wallet-detail').eq(0);
                    newWallet.find('.wallet-name').append('<sup style="color:red;">New</sup>');
                    newWallet.one('click', function () {
                        newWallet.find('sup').remove();
                    });
                    newWallet.add(newDetail).slideUp(0);
                    setTimeout(() => {
                        newWallet.slideDown();
                    }, 400);
                }
                $('#form-add-wallet').trigger('reset');
                hideModal($('#modal-add-wallet'));
            }
        });
    }
}

// for family new wallet
function submitNewWalletFam() {
    const checkResult = $('#form-add-fam-wallet .form-input').validateEmptyInput();
    const inputEmpty = $(checkResult).map(function () { return this.toArray() });
    $('.error').remove();
    if (checkResult) {
        inputEmpty.after('<div class="error offset-5-5">This field is required.</div>');
    } else {
        const form = $('#form-add-fam-wallet')[0];
        const walletData = new FormData(form);
        $.ajax({
            type: "POST",
            url: "ajax/add-fam-wallet.php",
            data: walletData,
            processData: false,
            cache: false,
            contentType: false,
            success: function (response) {
                if (!(response == 'fail' || response == 'empty')) {
                    const count = $('.family-wallet .card--content>*').length;
                    if (count == 1) {
                        $('.family-wallet .card--content').html(tableHeadFamily);
                    }
                    $('.family-wallet .table-head').after(response);
                    const newWallet = $('.family-wallet .wallet-item').eq(0);
                    const newDetail = $('.family-wallet .wallet-detail').eq(0);
                    newWallet.find('.wallet-name').append('<sup style="color:red;">New</sup>');
                    newWallet.one('click', function () {
                        newWallet.find('sup').remove();
                    });
                    newWallet.add(newDetail).slideUp(0);
                    setTimeout(() => {
                        newWallet.slideDown();
                    }, 400);
                }
                $('#form-add-fam-wallet').trigger('reset');
                hideModal($('#modal-add-fam-wallet'));
            }
        });
    }
}