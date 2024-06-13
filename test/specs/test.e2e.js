const { expect } = require('@wdio/globals')
const Page = require('../pageobjects/page')

describe('My update password application', () => {
    it('should be able to update a password', async () => {
        await browser.url('http://matomotest/update_password.php');

        const primaryAlert = await $('div.alert.alert-primary');
        await expect(primaryAlert).toHaveText('Update password');

        const password = await $('#passwordBox');
        const confirmPassword = await $('#confirmPassword');
        const submit = await $('#submit');

        await password.setValue('password');

        let pwError = await $('#password_error');
        await expect(pwError).toHaveText('Password must be at least 5 characters long and contain a number');
        await expect(submit).not.toBeClickable();

        await password.setValue('password1');
        await expect(pwError).not.toHaveText('Password must be at least 5 characters long and contain a number');
        await expect(submit).not.toBeClickable();

        let matchingPwError = await $('#matching_password_error');

        await confirmPassword.setValue('password');
        await expect(matchingPwError).toHaveText('Password need to match');
        await expect(submit).not.toBeClickable();

        await confirmPassword.setValue('password1');
        await expect(matchingPwError).not.toHaveText('Password need to match');
        await expect(submit).toBeClickable();
    })
})

