$(function () {
    $('#login-form input').on('focusin', function () {
        const inputId = $(this).attr('id');
        $('.input-label[for="' + inputId + '"').addClass('label-focus');
        $(this).parent().addClass('input-focus');
    }).on('focusout', function () {
        const inputId = $(this).attr('id');
        if ($(this).val() == '') {
            $('.input-label[for="' + inputId + '"').removeClass('label-focus');
            $(this).parent().removeClass('input-focus');
        };
    });

    const inputsFilled = $('#login-form input').filter(function () {
        return $(this).val() !== '';
    });

    for (let i = 0; i < inputsFilled.length; i++) {
        const id = inputsFilled.eq(i).attr('id');
        $('.input-label[for="' + id + '"').addClass('label-focus');
        $(this).parent().addClass('input-focus');
    }

    $('.toggle-pw').on('click', function () {
        let pwType = $('#password').attr('type');
        const icon = $(this).children();

        if (pwType == 'password') {
            $('#password').attr('type', 'text');
            icon.removeClass('fa-eye-slash').addClass('fa-eye')
            pwType = 'text';
        } else {
            $('#password').attr('type', 'password');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
            pwType = 'password';
        }
    });

    $('.password').on('paste', function (e) {
        e.preventDefault();
    });

    function validateLogin() {
        for (let i = 0; i < $('#login-form input').length; i++) {
            checkEmpty($('#login-form input').eq(i));
        }
        validateEmail($('#email'), 'login');
    }

    const $errWrapper = $('#password').parents().eq(1);
    const $errDiv = $errWrapper.children('.input-error');

    $('#login-form').on('submit', function (e) {
        e.preventDefault();
        validateLogin();
        if ($('#login-form .input-error').length <= 0) {
            $.ajax({
                type: "POST",
                url: "login-submit.php",
                data: {
                    'email': $('#email').val(),
                    'password': $('#password').val()
                },
                success: function (response) {
                    if (response != 'admin' && response != 'user') {
                        attachErrDiv($errDiv, $errWrapper, response);
                    } else if (response == 'admin') {
                        removeErrDiv($errDiv);
                        window.location.href = 'admin.php';
                    } else if (response == 'user') {
                        removeErrDiv($errDiv);
                        window.location.href = 'transaction.php';
                    }
                }
            });
        }
    });
});