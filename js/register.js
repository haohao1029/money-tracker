$(function () {
    $('#register-form input').on('focusin', function () {
        const inputId = $(this).attr('id');
        $('.input-label[for="' + inputId + '"').addClass('label-focus');
        $(this).parent().addClass('input-focus');
    }).on('focusout', function () {
        const inputId = $(this).attr('id');
        if ($(this).val() == '') {
            $('.input-label[for="' + inputId + '"').removeClass('label-focus');
            $(this).parent().removeClass('input-focus');
        };
        checkEmpty($(this));
    });

    const inputsFilled = $('#register-form input').filter(function () {
        return $(this).val() !== '';
    });

    for (let i = 0; i < inputsFilled.length; i++) {
        const id = inputsFilled.eq(i).attr('id');
        $('.input-label[for="' + id + '"').addClass('label-focus');
        $(this).parent().addClass('input-focus');
    };

    $('.toggle-pw').on('click', function () {
        const i = $('.toggle-pw').index($(this));
        const input = $('.password').eq(i);
        let pwType = input.attr('type');
        const icon = $(this).children();

        if (pwType == 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye-slash').addClass('fa-eye')
            pwType = 'text';
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
            pwType = 'password';
        }
    });

    $('.password').on('paste', function (e) {
        e.preventDefault();
    });

    $('.password').on('focusout', function () {
        checkPwMatch($('#password'), $('#confirm_password'));
    });

    function checkBeforeSubmit() {
        for (let i = 0; i < $('#register-form input').length; i++) {
            checkEmpty($('#register-form input').eq(i));
        }
        checkPwMatch($('#password'), $('#confirm_password'));
        validateUserDataRepeat($('#username'), 'username');
        validateEmail($('#email'), 'register');
        validatePassword($('#password'), $('#confirm_password'));
    }

    function displayRegResult(response) {
        $('.form-wrapper').addClass('result-wrapper').html('<span><i class="fal"></i></span>');
        let wrapperClass, resultMsg, iconClass, strBtn, linkBtn;
        if (response == 'success') {
            wrapperClass = 'result-success';
            resultMsg = 'Hooray!<br>Account successfully registered!';
            iconClass = 'fa-check-circle';
            strBtn = 'Login Now!';
            linkBtn = 'login.php';
        } else if (response == 'fail') {
            wrapperClass = 'result-fail';
            resultMsg = 'Oops...<br>Registration failed! <i class="fal fa-sad-tear" id="sad"></i><br>Please try again.';
            iconClass = 'fa-times-circle';
            strBtn = 'Try Again';
            linkBtn = 'register.php';
        }
        $('.form-wrapper').addClass(wrapperClass + ' animated bounceIn delay-1s');
        $('.form-wrapper i').addClass(iconClass);
        $('.form-wrapper span').append(resultMsg);
        $('.form-wrapper').append('<div><a href="' + linkBtn + '" class="btn-form" id="btn-result">' + strBtn + '</a></div>');
    }

    $('#register-form').on('submit', function (e) {
        e.preventDefault();
        checkBeforeSubmit();
        if ($('#register-form .input-error').length <= 0) { // submit form if no error
            $.ajax({
                type: "POST",
                url: "register-submit.php",
                data: {
                    'username': $('#username').val(),
                    'email': $('#email').val(),
                    'password': $('#password').val()
                },
                success: function (response) {
                    displayRegResult(response);
                }
            });
        }
    });
});