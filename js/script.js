let pwError = document.getElementById('password_error');
let pwConfirmError = document.getElementById('matching_password_error');
let submit = document.getElementById('submit');

pwError.style.display = 'none';
pwConfirmError.style.display = 'none';

let password = document.getElementById('passwordBox');

// disable submit button if the form input fails validation
function submitButtonActivated() {
    if (!password.value || !passwordConfirm.value) {
        submit.disabled = true;
    } else if (password.value !== passwordConfirm.value) {
        submit.disabled = true;
    } else {
        submit.disabled = false;
    }
}

submitButtonActivated();

// toggle the error messages on user input
password.addEventListener('input', function() {
    if (password.value.length < 5 || password.value.match(/\d+/) === null) {
        pwError.style.display = 'inline';
    } else {
        pwError.style.display = 'none';
    }
    submitButtonActivated();
});

let passwordConfirm = document.getElementById('confirmPassword');
passwordConfirm.addEventListener('input', function() {
    if (password.value !== passwordConfirm.value) {
        pwConfirmError.style.display = 'inline';
    } else {
        pwConfirmError.style.display = 'none';
    }
    submitButtonActivated();
});
