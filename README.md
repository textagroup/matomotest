# Matomo Update Password Test

My attempt at improving a basic update password script using PHP, Javascript, Bootstrap and automated testing.
This has been stored in a Git repo so you can see the commit history to see how I approached this test.

## Installation

A basic LAMP environment is needed to run this code I have used a .env file to store the database details and a example of my .env file can be seen below

```
DB_HOST="localhost"
DB="my_site"
DB_USER="db_user"
DB_PASSWORD="*******"
```

I am using phpdotenv to read the .env files and PHPUnit for backend unit testing so composer install will need to be run to install
these dependencies.

```
composer install
```

The WebdriverIO test relies on npm packages so these will need to be installed with following command.

```
npm install
```

## PHP

The PHP code for the script has been moved into it's own class called PasswordUtils.

If a instance of this class is created, it will connect to the DB (providing the .env file is correct) and check if 
the user table exists the table will be created if it does not exist.

The HTML for the update password page is returned by calling a method called fetchTemplate there is a script called update_password.php
which gives an example of the class being used.

There is another method testCookie being called which sets a cookie with an ID which defaults to 1 but this can be overridden with a different ID by providing the
ID as a parameter to the method.

## Testing

A PHPUnit test has been written and can be run by running the following from a command line.

```
# with db details stored in phpunit.xml
vendor/bin/phpunit test/phpunit/

# with db details set on the command line
DB_HOST=localhost DB_USER=test_db_user DB_PASSWORD=******** DB=my_test_site vendor/bin/phpunit test/phpunit
```

I have also created a basic WebdriverIO test which can be run with the following commands on different browsers.

```
#chrome
npm run wdio

#use a different URL
npm run wdio --url=http://mydevurl

#firefox
npx wdio ./wdio.firefox.conf.js

#chromium
npx wdio ./wdio.chromium.conf.js
```


## Templates

The HTML for the update password has been moved into a HTML template which has a few placeholders
these are replaced with their respective values.

\#NAME\# is replaced with the name of the user based on the cookie user ID.

\#MESSAGE\# is a confirmation or error message that is generated from the PHP code.

\#TOKEN\# is a CSRF token used to secure the form

You can supply the method fetchTemplate with a param to use a different template other than the default template.

```
$html = $passwordUtils->fetchTemplate('/path/to/custom/template.html');
```

## Security considerations

The PasswordUtils class expects a token to have been set when the form is submitted this is taken care of in
the default template  but can be implemented by adding the \#TOKEN\# placeholder in any customised templates.

The password is stored in the user table after it has been hashed by the standard password_hash method in PHP.

## Javascript

Vanilla Javascript is being used to validate the passwords match and meet the password criteria.

The Javascript is stored in js/script.js

## Styling

Basic Bootstrap styling has been used for the default template.

## Potential improvements

Further improvements could be made to the functionality and some of these are listed below.

- Add a password strength meter
- Add a password generator
- Use an existing library like [phppass](https://github.com/rchouinard/phpass)

## Demo video

Demo video of form nbeing uses and PHPUnit and WebdriverIO tests running

![Demo](demo.webm)
