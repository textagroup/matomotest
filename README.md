# Matomo Update Password Test

My attempt at improving a basix update password script using PHP, Javascript, Bootstrap and automated testing.
This has been stored in a Git repo so you can see the commit history to see how I approached this test.

## Installation

A basic LAMP environment is needed to run this code I have used a .env file to store the database details and a example of my .env file can be seen below

```
DB_HOST="localhost"
DB="my_site"
DB_USER="db_user"
DB_PASSWORD="*******"
```

I am using phpdotenv to read the .env files and PHPUnit for backwend unit testing so composer install will need to be run to install
these dependencies.

```composer install```

## PHP

The PHP code for the script has been moved into it's own class call PasswordUtils.
If a instance of this class is created it will connect to the DB (providing the .env file is correct) and check if 
the user tables exists and creates it if it does not exist.
The html for the update password page is returned by calling a method called fetchTemplate there is a script called update_password.php
which gives an example of the class being used.
There is another method testCookie being called which sets a cookie with a id which defaults to 1 but that can be overridden.
