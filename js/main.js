$(function () {
    const prevSideNav = $('#side-nav').prevAll();
    const nextSideNav = $('#side-nav').nextAll();
    const prevSideNavChild = prevSideNav.add(prevSideNav.find('*'));
    const nextSideNavChild = nextSideNav.add(nextSideNav.find('*'));
    const exceptSideNav = prevSideNav.add(nextSideNav);
    const allExceptSideNav = exceptSideNav.add(prevSideNavChild).add(nextSideNavChild);

    let sideNavOn = false;
    function toggleSideNav() {
        if (sideNavOn) {
            $('#side-nav').removeClass('active');
            exceptSideNav.css({
                'filter': 'unset',
                'transition': 'filter .3s'
            });
            $('.side-nav-toggle').removeClass('is-active');
            $('#content').disablescroll('undo');
            sideNavOn = false;
        } else {
            $('#side-nav').addClass('active');
            exceptSideNav.css({
                'filter': 'brightness(40%)',
                'transition': 'filter .3s'
            });
            $('.side-nav-toggle').addClass('is-active');
            $('#content').disablescroll();
            sideNavOn = true;
        }
    }
    $('.side-nav-toggle').on('click', function () {
        if (notiOn) {
            toggleNoti();
        }
        toggleSideNav();
        clickEffect($(this));
    });
    allExceptSideNav.on('touchstart', function (e) {
        if (sideNavOn) {
            e.preventDefault();
            e.stopImmediatePropagation();
            toggleSideNav();
        }
    });

    let optionOn = false;
    function toggleOption() {
        if (optionOn) {
            $('.option-wrapper').css('transform', 'scale(0)');
            optionOn = false;
        } else {
            $('.option-wrapper').css('transform', 'scale(1)');
            optionOn = true;
        }
    }
    $('.notification').on('click', function () {
        clickEffect($(this));
    });
    $('.option-toggle').on('click', function () {
        if (notiOn) {
            toggleNoti();
        }
        toggleOption();
        clickEffect($(this));
    });

    $('#content').on('mousedown touchstart', function (e) {
        if (optionOn) {
            e.preventDefault();
            toggleOption();
        }
        if (notiOn) {
            e.preventDefault();
            toggleNoti();
        }
    });

    $(window).keydown(function (e) {
        if (e.key == 'Escape') {
            if (sideNavOn) {
                toggleSideNav();
            }
            if (optionOn) {
                toggleOption();
            }
            if (notiOn) {
                toggleNoti();
            }
        };
    });

    // notification START
    let notiOn = false;
    function toggleNoti() {
        if (notiOn) {
            $('#noti-panel').removeClass('show');
            notiOn = false;
        } else {
            $('#noti-panel').addClass('show');
            notiOn = true;
        }
    }

    $('.notification').on('click', function () {
        if (optionOn) {
            toggleOption();
        }
        toggleNoti();
    });

    // view budget plan detail
    $(document).on('click', '.plan-item, .noti-item', function () {
        const planID = $(this).attr('data-value');
        $.ajax({
            type: "POST",
            url: "ajax/budget-detail.php",
            data: {
                'plan_id': planID
            },
            success: function (response) {
                response = JSON.parse(response);
                const name = response['plan_name'];
                const cat_name = response['cat_name'];
                const wall_name = response['wall_name'];
                const amount = response['amount'];
                const alert = response['alert'];
                const spent = response['spent'];
                let percent = response['percent'];
                const start = response['start'];
                const end = response['end'];

                // put into html
                $('.detail-name').html(name);
                $('.detail-categ').html(cat_name);
                $('.detail-wallet').html(wall_name);
                $('.detail-amount').html(amount);
                if (alert) {
                    $('.detail-alert').html(alert + " %");
                } else {
                    $('.detail-alert').html("-");
                }
                if (spent) {
                    $('.detail-spent').html(spent);
                } else {
                    $('.detail-spent').html("0.00");
                }

                $('.detail-start').html(start);
                if (end) {
                    $('.detail-end').html(end);
                } else {
                    $('.detail-end').html("-");
                }
                $('#btn-edit-budget, #btn-delete-budget').val(planID);

                $('#spentBar').html('');
                // spentBar
                var bar = new ProgressBar.Line(spentBar, {
                    strokeWidth: 4,
                    easing: 'easeInOut',
                    duration: 1400,
                    color: '#82ff86',
                    trailColor: '#eee',
                    trailWidth: 1,
                    svgStyle: { width: '100%', height: '100%' },
                    text: {
                        style: {
                            color: '#999',
                            position: 'absolute',
                            right: '0',
                            top: '30px',
                            padding: 0,
                            margin: 0,
                            transform: null
                        },
                        autoStyleContainer: false
                    },
                    from: { color: '#28A745' },
                    to: { color: '#f00' },
                    step: (state, bar) => {
                        bar.path.setAttribute('stroke', state.color);
                    }
                });
                $('#spentBar').append('<div id="spentNo"></div>');
                $('#spentNo').html(percent);
                percent /= 100;
                if (percent > 1) {
                    bar.animate(1.0);
                } else {
                    bar.animate(percent);
                }
                $({ Counter: 0 }).animate({
                    Counter: $('#spentNo').text()
                }, {
                    duration: 1400,
                    easing: 'swing',
                    step: function () {
                        $('#spentNo').text(Math.round(this.Counter) + " %");
                    }
                });
            }
        });
        $('#modal-view-budget').modal();
    });

    $('#modal-view-budget').on('show.bs.modal', function () {
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
    // notification END
});

$(window).on('keydown', function (e) {
    if (e.key == 'Escape') {
        $('.modal').each(function () {
            if ($(this).hasClass('show')) {
                hideModal($(this));
            }
        });
    }
});

function hideModal(modal) {
    modal.addClass('bounceOutLeft').removeClass('fadeInRight bounceIn bounceInUp');
    $('.modal-backdrop').removeClass('show');

    modal.on('animationend', function () {
        $('.modal-backdrop').remove();
        modal.modal('hide');
    });
}

function clickEffect(button, size) {
    const effectEl = button.children('.clicked');
    if (size === undefined) {
        effectEl.animate({
            width: '40px',
            height: '40px'
        }, 200, 'linear');
    } else {
        effectEl.animate({
            width: size + 'px',
            height: size + 'px'
        }, 200, 'linear');
    }
    effectEl.animate({
        opacity: '0'
    }, 100, 'linear').animate({
        height: '0',
        width: '0',
        opacity: '1'
    }, 0, 'linear');
    setTimeout(() => {
        effectEl.css('display', '')
    }, 310);
}

//// reference: http://jsfiddle.net/CxdUQ/
$(document).on('show.bs.modal', '.modal', function () {
    var zIndex = 1040 + (10 * $('.modal:visible').length);
    $(this).css('z-index', zIndex);
    setTimeout(function () {
        $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
    }, 0);
});
////

//// reference: https://stackoverflow.com/a/4835406/11386875
function decodeHtmlEntity(text) {
    var map = {
        '&amp;': '&',
        '&lt;': '<',
        '&gt;': '>',
        '&quot;': '"',
        '&#039;': "'"
    };

    return text.replace(/(&amp;|&lt;|&gt;|&quot;|&#039;)/g, function (m) {
        return map[m];
    });
}
////

/**
 * @author: TP051350
 * @description: functions for various form validation in the system
 */
function removeErrDiv($errDiv) { // function for removing error message
    if ($errDiv.length > 0) {
        $errDiv.remove();
    }
}

function attachErrDiv($errDiv, $wrapper, strError) { // function for attaching error message
    if ($errDiv.length <= 0) {
        $wrapper.append('<div class="input-error">' + strError + '</div>');
    } else {
        $errDiv.html(strError);
    }
}

function checkEmpty($input) { // check whether input is empty / contain only whitespace
    const strError = 'Please fill out this field.';
    const $wrapper = $input.parents().eq(1);
    const $errDiv = $wrapper.children('.input-error');
    if (($input.val() == '') || (!$input.val().replace(/\s/g, '').length)) {
        attachErrDiv($errDiv, $wrapper, strError);
    } else {
        removeErrDiv($errDiv);
    };
    // use for loop if checking multiple input
    // Example:
    // =============================================
    // for (let i = 0; i < $('input').length; i++) {
    //     checkEmpty($('input').eq(i));
    // }
}

function checkPwMatch($inputPw, $inputCfm) { // check whether pw and cfmpw match
    const strError = 'Confirmation password does not matches!';
    const $wrapper = $inputCfm.parents().eq(1);
    const $errDiv = $wrapper.children('.input-error');
    if (($inputPw.val() !== '') && ($inputCfm.val() !== '')) {
        if ($inputPw.val() !== $inputCfm.val()) {
            attachErrDiv($errDiv, $wrapper, strError);
        } else {
            removeErrDiv($errDiv);
        }
    }
}

function validateEmail($inputEmail, mode, userID) { // ajax check email format
    // if mode = 'register', check email format and email used
    // if mode = 'login', only check email format
    // if mode = 'edit', same as register but with different error message
    // userID is optional, used when editing existing data of a user
    const $emailWrapper = $inputEmail.parents().eq(1);
    const $errDiv = $emailWrapper.children('.input-error');
    if (userID === undefined) {
        userID = '';
    }
    $.ajax({
        type: "POST",
        url: "validation/validate-email.php",
        async: false,
        data: {
            'email': $('#email').val(),
            'mode': mode,
            'user_id': userID
        },
        success: function (response) {
            if (response == 'empty') {
                checkEmpty($inputEmail);
            } else if (response !== 'passed') {
                attachErrDiv($errDiv, $emailWrapper, response);
            } else if (response == 'passed') {
                removeErrDiv($errDiv);
            }
        }
    });
}

function validatePassword($inputPw, $inputCfm) { // ajax check if both password match
    const $cfmPwWrapper = $inputCfm.parents().eq(1);
    const $errDiv = $cfmPwWrapper.children('.input-error');
    $.ajax({
        type: "POST",
        url: "validation/validate-password.php",
        async: false,
        data: {
            'password': $inputPw.val(),
            'cfm_password': $inputCfm.val()
        },
        success: function (response) {
            if (response == 'empty') {
                checkEmpty($inputPw);
                checkEmpty($inputCfm);
            } else if (response !== 'passed') {
                attachErrDiv($errDiv, $cfmPwWrapper, response);
            } else if (response == 'passed') {
                removeErrDiv($errDiv);
            }
        }
    });
}

function validateUserDataRepeat($input, dataName, userID) { // check if the user data is repeated in database
    // attribute: 'username', 'phone number'
    // userID is optional, used when editing existing data of a user
    const $inputWrapper = $input.parents().eq(1);
    const $errDiv = $inputWrapper.children('.input-error');
    if (userID === undefined) {
        userID = '';
    }
    if ($errDiv.length > 0) {
        return;
    }
    $.ajax({
        type: "POST",
        url: "validation/validate-data-repeat.php",
        async: false,
        data: {
            'data': $input.val(),
            'attribute': dataName,
            'user_id': userID
        },
        success: function (response) {
            if (response == 'empty') {
                checkEmpty($input);
            } else if (response !== 'passed') {
                attachErrDiv($errDiv, $inputWrapper, response);
            } else if (response == 'passed') {
                removeErrDiv($errDiv);
            }
        }
    });
}

function checkPwCorrect($inputPw) { // check whther the password enter is correct
    const $inputPwWrapper = $inputPw.parents().eq(1);
    const $errDiv = $inputPwWrapper.children('.input-error');
    const strErr = 'Incorrect password';
    $.ajax({
        type: "POST",
        url: "validation/check-password.php",
        async: false,
        data: {
            'password': $inputPw.val()
        },
        success: function (response) {
            if (response == 'empty') {
                checkEmpty($inputPw);
            } else if (response == 'wrong') { // wrong password
                attachErrDiv($errDiv, $inputPwWrapper, strErr);
            } else if (response == 'correct') { // correct password
                removeErrDiv($errDiv);
            } else if (response == 'fail') { // sql execute fail
                alert('Error occurs! Reloading the page.');
                location.reload();
            }
        }
    });
}

// jQuery plugin
/**
 * @author: TP051350
 * @description: 
 * return the input element if it is empty, 
 * return false if there's no empty input
 */
(function (jQuery) {
    jQuery.fn.validateEmptyInput = function () {
        let emptyInput = [];
        this.each(function () {
            const value = $(this).val();

            if (value == '' || value == undefined || value == null || !value.replace(/\s/g, '').length) {
                emptyInput.push($(this));
            }
        });

        if (emptyInput.length) {
            return emptyInput;
        } else {
            return false;
        }
    };
})(jQuery);

(function ($) { // plugin for setting filter for input
    $.fn.inputFilter = function (inputFilter) {
        return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function () {
            if (inputFilter(this.value)) {
                this.oldValue = this.value;
                this.oldSelectionStart = this.selectionStart;
                this.oldSelectionEnd = this.selectionEnd;
            } else if (this.hasOwnProperty("oldValue")) {
                this.value = this.oldValue;
                this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
            }
        });
    };
}(jQuery));

/**
 * $.disablescroll
 * @author: Josh Harrison - aloof.co
 * @description:
 * Disables scroll events from mousewheels, touchmoves and keypresses.
 * Use while jQuery is animating the scroll position for a guaranteed super-smooth ride!
 */
(function (e) { "use strict"; function r(t, n) { this.opts = e.extend({ handleWheel: !0, handleScrollbar: !0, handleKeys: !0, scrollEventKeys: [32, 33, 34, 35, 36, 37, 38, 39, 40] }, n); this.$container = t; this.$document = e(document); this.lockToScrollPos = [0, 0]; this.disable() } var t, n; n = r.prototype; n.disable = function () { var e = this; e.opts.handleWheel && e.$container.on("mousewheel.disablescroll DOMMouseScroll.disablescroll touchmove.disablescroll", e._handleWheel); if (e.opts.handleScrollbar) { e.lockToScrollPos = [e.$container.scrollLeft(), e.$container.scrollTop()]; e.$container.on("scroll.disablescroll", function () { e._handleScrollbar.call(e) }) } e.opts.handleKeys && e.$document.on("keydown.disablescroll", function (t) { e._handleKeydown.call(e, t) }) }; n.undo = function () { var e = this; e.$container.off(".disablescroll"); e.opts.handleKeys && e.$document.off(".disablescroll") }; n._handleWheel = function (e) { e.preventDefault() }; n._handleScrollbar = function () { this.$container.scrollLeft(this.lockToScrollPos[0]); this.$container.scrollTop(this.lockToScrollPos[1]) }; n._handleKeydown = function (e) { for (var t = 0; t < this.opts.scrollEventKeys.length; t++)if (e.keyCode === this.opts.scrollEventKeys[t]) { e.preventDefault(); return } }; e.fn.disablescroll = function (e) { !t && (typeof e == "object" || !e) && (t = new r(this, e)); t && typeof e == "undefined" ? t.disable() : t && t[e] && t[e].call(t); return this }; window.UserScrollDisabler = r })(jQuery);