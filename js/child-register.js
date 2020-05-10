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
    if ($("#password") != $('#confirm_password')) {
        $(".register").attr("disabled", true);
    }
    function checkBeforeSubmit() {
        for (let i = 0; i < $('#register-form input').length; i++) {
            checkEmpty($('#register-form input').eq(i));
        }
        checkPwMatch($('#password'), $('#confirm_password'));
        validateUserDataRepeat($('#username'), 'username');
        validateEmail($('#email'), 'register');
        validatePassword($('#password'), $('#confirm_password'));
    }

});