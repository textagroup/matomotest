let pwError = document.getElementById('password_error');
let pwConfirmError = document.getElementById('matching_password_error');
let submit = document.getElementById('submit');

pwError.style.display = 'none';
pwConfirmError.style.display = 'none';

let password = document.getElementById('passwordBox');
password.addEventListener('input', function() {
    if (password.value.length < 5 || password.value.match(/\d+/) === null) {
        pwError.style.display = 'inline';
        submit.disabled = true;
    } else {
        pwError.style.display = 'none';
        submit.disabled = false;
    }
});

let passwordConfirm = document.getElementById('confirmPassword');
passwordConfirm.addEventListener('input', function() {
    if (password.value !== passwordConfirm.value) {
        pwConfirmError.style.display = 'inline';
        submit.disabled = true;
    } else {
        pwConfirmError.style.display = 'none';
        submit.disabled = false;
    }
});

// TODO Fix issues where the submit button is enabled but the values are false
