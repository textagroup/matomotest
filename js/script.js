let pwError = document.getElementById('password_error');
let pwConfirmError = document.getElementById('matching_password_error');

pwError.style.display = 'none';
pwConfirmError.style.display = 'none';

let password = document.getElementById('passwordBox');
password.addEventListener('input', function() {
    if (password.value.length < 5 || password.value.match(/\d+/) === null) {
        pwError.style.display = 'inline';
    } else {
        pwError.style.display = 'none';
    }
});

let passwordConfirm = document.getElementById('confirmPassword');
passwordConfirm.addEventListener('input', function() {
    if (password.value !== passwordConfirm.value) {
        pwConfirmError.style.display = 'inline';
    } else {
        pwConfirmError.style.display = 'none';
    }
});
