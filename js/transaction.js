$(function () {
    $('.btn-eff').on('click', function () {
        if ($(this).attr('data-effect') == 'click-l') {
            clickEffect($(this), 50);
        } else {
            clickEffect($(this));
        }
    });

    $('.prev-month, .next-month, #current-month').on('click', function () {
        const value = $(this).val();
        const dateShowing = $('.showing-month').attr('data-value');
        refreshTrans(dateShowing, value);
    });

    // modal
    const modalDetail = $('#modal-show-trans');
    const modalAddTrans = $('#modal-add-trans');
    const modalDelete = $('#modal-confirm-delete');
    const modalEditTrans = $('#modal-edit-trans');

    $('body').on('click', '.trans-item', function () {
        const transID = $(this).attr('data-value');
        $.ajax({
            type: "POST",
            url: "ajax/transaction-detail.php",
            data: {
                'trans_id': transID
            },
            success: function (response) {
                response = JSON.parse(response);
                const category = response['category'];
                const date = response['date'];
                const type = response['type'];
                const wallet = response['wallet'];
                const amount = response['amount'];
                const desc = response['desc'];

                $('.detail-category').html(category);
                $('.detail-date').html(date);
                $('.detail-type').html(type);
                $('.detail-wallet').html(wallet);
                $('.detail-amount').html(amount);
                $('.detail-desc').html(desc);
                $('.btn-delete, #btn-edit').val(transID);
            }
        });
        modalDetail.modal();
    });

    modalDetail.on('show.bs.modal', function () {
        $('#modal-show-trans .btn-modal-close').one('click', function () {
            hideModal(modalDetail);
        });
    }).on('shown.bs.modal', function () {
        $('.modal-backdrop').addClass('custom-transition');
    }).on('hidden.bs.modal', function () {
        modalDetail.removeClass('bounceOutLeft').addClass('bounceIn').off('animationend');
        $('#modal-show-trans .btn-modal-close').off('click');
        $('.modal-backdrop').removeClass('custom-transition');
    });

    // delete trans
    $('.btn-delete').on('click', function () {
        modalDelete.modal();
        const transID = $(this).val();

        $('#confirm-delete').on('click', function () {
            const showingDateString = $('.showing-month').attr('data-value');
            $.ajax({
                type: "POST",
                url: "ajax/delete-trans.php",
                data: {
                    'trans_id': transID
                },
                success: function (response) {
                    if (response == 'exceed-weak') { // exceed (children)
                        $('#modal-exceed .modal-body').html('Deleting this transaction record will result in overdraft of the wallet. You may ask your parents (Head of the family) to top up your wallet and try again.');
                        $('#modal-exceed').modal();
                        return;
                    } else if (response == 'exceed-strong') { // exceed (parent / individual)
                        $('#modal-exceed .modal-body').html('Deleting this transaction record will result in overdraft of the wallet. You may add an income transaction record with "Top Up" category and try again.');
                        $('#modal-exceed').modal();
                        return;
                    }
                    response = JSON.parse(response);
                    const status = response['status'];
                    const alertCount = response['alert_count'];
                    const notiEl = response['noti_el'];

                    $('.notification i').attr('data-after', alertCount);
                    if (alertCount) {
                        $('.notification i').removeClass('no-alert');
                    } else {
                        $('.notification i').addClass('no-alert');
                    }

                    $('.noti-body').html(notiEl);

                    if (status == 'success') {
                        modalDelete.modal('hide');
                        hideModal(modalDetail);
                        refreshTrans(showingDateString);
                    } else if (status == 'fail') {
                        alert('Oops! Some error(s) occur. Please try again.');
                        location.reload();
                        return;
                    }
                }
            });
        });

        $('#modal-confirm-delete button[data-dismiss="modal"]').on('click', function () {
            $('#confirm-delete').off('click');
        });
    });

    // add trans
    $('#add-trans').on('click', function () {
        modalAddTrans.modal();
        $.ajax({
            type: "POST",
            url: "ajax/fetch-wallet.php",
            success: function (response) {
                if (response != 'error') {
                    const placeholder = $('#wallet .select-placeholder').detach();
                    $('#wallet').html(response).prepend(placeholder);
                    $('#wallet .select-placeholder').prop('selected', true);
                }
            }
        });

        let dateChanged = false;
        $('#date').on({
            'focusin': function () {
                $(this).attr({
                    'value': $(this).attr('data-value'),
                    'type': 'date'
                });
            }, 'focusout': function () {
                if (!dateChanged) {
                    $(this).attr({
                        'type': 'text',
                        'value': 'today'
                    });
                }
            }, 'change': function () {
                dateChanged = true;
            }
        });

        $('#type').on('change', function () {
            const transType = $(this).val();
            $.ajax({
                type: "POST",
                url: "ajax/fetch-category.php",
                data: {
                    'trans_type': transType
                },
                success: function (response) {
                    if (response != 'error') {
                        $('#category .select-placeholder').html('- Select a category -');
                        const placeholder = $('#category .select-placeholder').detach();
                        $('#category').prop('disabled', false).html(response).prepend(placeholder);
                        $('#category .select-placeholder').prop('selected', true);
                    }
                }
            });
        });
    });

    modalAddTrans.on('show.bs.modal', function () {
        $('#modal-add-trans .btn-modal-close').one('click', function () {
            hideModal(modalAddTrans);
        });
    }).on('shown.bs.modal', function () {
        $('.modal-backdrop').addClass('custom-transition');
    }).on('hidden.bs.modal', function () {
        modalAddTrans.removeClass('bounceOutLeft').addClass('bounceInUp').off('animationend');
        $('#modal-add-trans .btn-modal-close').off('click');
        $('.modal-backdrop').removeClass('custom-transition');
        $('#date').attr({
            'type': 'text',
            'value': 'today'
        });
        $('#form-add-trans').trigger('reset');
    });

    $("#amount").inputFilter(function (value) {
        return /^-?\d*[.,]?\d{0,2}$/.test(value);
    });

    $('#form-add-trans').on({
        'submit': function (e) {
            e.preventDefault();
            validateOrSubmit();
            $('#form-add-trans .form-input').on('change', function () {
                const checkResult = $('#form-add-trans .form-input').validateEmptyInput();
                const inputEmpty = $(checkResult).map(function () { return this.toArray() });
                $('.error').remove();
                if (checkResult) {
                    inputEmpty.after('<div class="error offset-4">This field is required.</div>');
                }
            });
        }, 'reset': function () {
            $('.error').remove();
            $('#form-add-trans .form-input').off('change');
            $('#category .select-placeholder').html('- Select a type first -');
            const placeholder = $('#category .select-placeholder').detach();
            $('#category').prop('disabled', true).empty().append(placeholder);
            $('#wallet .select-placeholder').prop('selected', true);
        }
    });

    // edit trans
    $('#btn-edit').on('click', function () {
        hideModal(modalDetail);
        $.ajax({
            type: "POST",
            url: "ajax/fetch-wallet.php",
            success: function (response) {
                if (response != 'error') {
                    $('#wallet').html(response);
                }
            }
        });

        const transID = $(this).val();
        $.ajax({
            type: "POST",
            url: "ajax/transaction-detail.php",
            data: {
                'trans_id': transID
            },
            success: function (response) {
                $('#trans_id').val(transID);
                response = JSON.parse(response);
                const catID = response['cat_id'];
                let date = response['date'];
                const type = response['type'];
                const walletID = response['wallet_id'];
                const amount = response['amount'];
                const desc = decodeHtmlEntity(response['desc']);

                // reformat date from mm-dd-yyyy to yyyy-mm-dd
                date = date.match(/[0-9]{2}[\-][0-9]{2}[\-][0-9]{4}/);
                const [dd, mm, yyyy] = date[0].split('-');
                const val_date = `${yyyy}-${mm}-${dd}`;

                // put into form
                $('#edit-type option[value="' + type + '"]').prop('selected', true);
                $.ajax({
                    type: "POST",
                    url: "ajax/fetch-category.php",
                    async: false,
                    data: {
                        'trans_type': type
                    },
                    success: function (response) {
                        if (response != 'error') {
                            $('#edit-category').html(response);
                        }
                    }
                });
                $('#edit-category option[value="' + catID + '"]').prop('selected', true);
                $.ajax({
                    type: "POST",
                    url: "ajax/fetch-wallet.php",
                    async: false,
                    success: function (response) {
                        if (response != 'error') {
                            $('#edit-wallet').html(response);
                        }
                    }
                });
                $('#edit-wallet option[value="' + walletID + '"]').prop('selected', true);
                $('#edit-amount').val(amount);
                $('#edit-date').val(val_date);
                $('#edit-description').val(desc);
            }
        });

        $('#edit-type').on('change', function () {
            const transType = $(this).val();
            $.ajax({
                type: "POST",
                url: "ajax/fetch-category.php",
                data: {
                    'trans_type': transType
                },
                success: function (response) {
                    if (response != 'error') {
                        $('#edit-category').html('<option selected disabled class="select-placeholder">- Select a category -</option>');
                        const placeholder = $('#edit-category .select-placeholder').detach();
                        $('#edit-category').html(response).prepend(placeholder);
                        $('#edit-category .select-placeholder').prop('selected', true);
                    }
                }
            });
        });

        $('#form-edit-trans').on({
            'submit': function (e) {
                e.preventDefault();
                validateSubmitEdit();
                $('#form-edit-trans .form-input').on('change', function () {
                    const checkResult = $('#form-edit-trans .form-input').validateEmptyInput();
                    const inputEmpty = $(checkResult).map(function () { return this.toArray() });
                    $('.error').remove();
                    if (checkResult) {
                        inputEmpty.after('<div class="error offset-4">This field is required.</div>');
                    }
                });
            }, 'reset': function () {
                $('.error').remove();
                $('#form-edit-trans .form-input').off('change');
                dateEdited(false);
            }
        });

        setTimeout(() => {
            modalEditTrans.modal();
        }, 550);
    });

    modalEditTrans.on('show.bs.modal', function () {
        $('#modal-edit-trans .btn-modal-close').one('click', function () {
            hideModal(modalEditTrans);
        });
    }).on('shown.bs.modal', function () {
        $('.modal-backdrop').addClass('custom-transition');
    }).on('hidden.bs.modal', function () {
        modalEditTrans.removeClass('bounceOutLeft').addClass('fadeInRight').off('animationend');
        $('#modal-edit-trans .btn-modal-close').off('click');
        $('.modal-backdrop').removeClass('custom-transition');
        $('#form-edit-trans').trigger('reset');
    });

    $(document).on('change', '#edit-date', function () {
        dateEdited(true);
    });

    $('#modal-exceed').on('show.bs.modal', function () {
        var zIndex = parseInt($(this).css('z-index'));
        setTimeout(() => {
            $(this).css('z-index', zIndex + 10);
            setTimeout(function () {
                $('.modal-stack:last').css('z-index', zIndex + 9).addClass('modal-stack');
            }, 0);
        }, 10);
    });
});

function validateOrSubmit() { // function for add form
    const checkResult = $('#form-add-trans .form-input').validateEmptyInput();
    const inputEmpty = $(checkResult).map(function () { return this.toArray() });
    $('.error').remove();
    if (checkResult) {
        inputEmpty.after('<div class="error offset-4">This field is required.</div>');
    } else {
        const form = $('#form-add-trans')[0];
        const transData = new FormData(form);
        $.ajax({
            type: "POST",
            url: "ajax/add-trans.php",
            data: transData,
            processData: false,
            cache: false,
            contentType: false,
            success: function (response) {
                if (response == 'empty') {
                    // only trigger when the user modifies the javascript validation
                    // and cause empty data to be submitted to php
                    alert('Oops! Some error(s) occur. Please try again.');
                    location.reload();
                    return;
                }

                if (response == 'exceed-weak') { // exceed (children)
                    $('#modal-exceed .modal-body').html('The amount entered exceeds the wallet balance. You may ask your parents (Head of the family) to top up your wallet and try again.');
                    $('#modal-exceed').modal();
                    return;
                } else if (response == 'exceed-strong') { // exceed (parent / individual)
                    $('#modal-exceed .modal-body').html('The amount entered exceeds the wallet balance. You may add an income transaction record with "Top Up" category and try again.');
                    $('#modal-exceed').modal();
                    return;
                }

                response = JSON.parse(response);
                const status = response['status'];
                const date = response['date'];
                const alertCount = response['alert_count'];
                const notiEl = response['noti_el'];

                const showingDateString = $('.showing-month').attr('data-value');

                if (status == 'success' && checkMonthYearMatch(date, showingDateString)) {
                    // refresh
                    refreshTrans(showingDateString);
                }

                // alert
                $('.notification i').attr('data-after', alertCount);
                if (alertCount) {
                    $('.notification i').removeClass('no-alert');
                } else {
                    $('.notification i').addClass('no-alert');
                }

                $('.noti-body').html(notiEl);

                $('#form-add-trans').trigger('reset');
                hideModal($('#modal-add-trans'));
            }
        });
    };
}

function validateSubmitEdit() { // function for edit form
    const checkResult = $('#form-edit-trans .form-input').validateEmptyInput();
    const inputEmpty = $(checkResult).map(function () { return this.toArray() });
    $('.error').remove();
    if (checkResult) {
        inputEmpty.after('<div class="error offset-4">This field is required.</div>');
    } else {
        const form = $('#form-edit-trans')[0];
        const transData = new FormData(form);
        $.ajax({
            type: "POST",
            url: "ajax/edit-trans.php",
            data: transData,
            processData: false,
            cache: false,
            contentType: false,
            success: function (response) {
                if (response == 'empty') {
                    // only trigger when the user modifies the javascript validation
                    // and cause empty data to be submitted to php
                    alert('Oops! Some error(s) occur. Please try again.');
                    location.reload();
                    return;
                }

                if (response == 'exceed-weak') { // exceed (children)
                    $('#modal-exceed .modal-body').html('The changing of transaction amount to the amount entered will result in overdraft of the wallet. You may ask your parents (Head of the family) to top up your wallet and try again.');
                    $('#modal-exceed').modal();
                    return;
                } else if (response == 'exceed-strong') { // exceed (parent / individual)
                    $('#modal-exceed .modal-body').html('The changing of transaction amount to the amount entered will result in overdraft of the wallet. You may add an income transaction record with "Top Up" category and try again.');
                    $('#modal-exceed').modal();
                    return;
                }

                response = JSON.parse(response);
                const status = response['status'];
                const date = response['date'];
                const alertCount = response['alert_count'];
                const notiEl = response['noti_el'];

                const showingDateString = $('.showing-month').attr('data-value');

                $('.notification i').attr('data-after', alertCount);
                if (alertCount) {
                    $('.notification i').removeClass('no-alert');
                } else {
                    $('.notification i').addClass('no-alert');
                }

                $('.noti-body').html(notiEl);

                if (status == 'success' && checkMonthYearMatch(date, showingDateString)) {
                    // refresh
                    refreshTrans(showingDateString);
                }
                $('#form-edit-trans').trigger('reset');
                hideModal($('#modal-edit-trans'));
                dateEdited(false);
            }
        });
    };
}

function dateEdited(boolean) {
    if (boolean) {
        $('#date_edited').val('true');
    } else {
        $('#date_edited').val('false');
    }
}

function refreshTrans(php_unix_timestamp, action) {
    if (action == undefined) {
        action = 'showing';
    }

    $.ajax({
        type: "POST",
        url: "ajax/transaction-change-month.php",
        data: {
            'date_showing': php_unix_timestamp,
            'action_val': action
        },
        success: function (response) {
            response = JSON.parse(response);
            const date = response['date'];
            const unix = response['unixdate'];
            const expenses = response['expenses'];
            const income = response['income'];
            const total = response['total'];

            $('.showing-month').attr('data-value', unix).html(date);
            $('.expenses .value').html(expenses);
            $('.income .value').html(income);
            $('.total .value').html(total);

            if (checkMonthYearMatch(unix)) {
                $('.next-month').prop('disabled', true);
            } else {
                $('.next-month').prop('disabled', false);
            }
            refreshTransList(unix);
        }
    });
}

function refreshTransList(php_unix_timestamp) {
    $.ajax({
        type: "POST",
        url: "ajax/transaction-update.php",
        data: {
            'target_unix': php_unix_timestamp
        },
        success: function (response) {
            $('#content>*:not(.control)').remove();
            $('#content').append(response);
        }
    });
}

/**
 * @description check if two timestamps' month and year match
 *              return true if match, false if not match
 * @param {number} first_unix_timestamp
 * @param {number} [second_unix_timestamp]
 */
function checkMonthYearMatch(first_unix_timestamp, second_unix_timestamp) {

    const stringFirstDate = new Date(first_unix_timestamp * 1000);
    const targetMonth = stringFirstDate.getMonth();
    const targetYear = stringFirstDate.getFullYear();

    let stringSecondDate;
    if (second_unix_timestamp == undefined) {
        stringSecondDate = new Date();
    } else {
        stringSecondDate = new Date(second_unix_timestamp * 1000);
    }
    const currentMonth = stringSecondDate.getMonth();
    const currentYear = stringSecondDate.getFullYear();

    if (targetMonth == currentMonth && targetYear == currentYear) {
        return true;
    } else {
        return false;
    }
}