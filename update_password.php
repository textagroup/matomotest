<html>
<head>
    <?php $connection = mysqli_connect('localhost', 'db_user', 'password!','my_site');
    if ($_REQUEST['password']) {
        mysqli_execute_query( $connection, "update user set password = '" . stripslashes($_REQUEST['password']) . "' where id = " . $_COOKIE['user_id'] );
        echo "<p>Password updated!</p>";
        die();
    } ?>
    <style>
        .em {color: orange}
    </style>
</head>
<body style="width: 100%; margin: 10%;">
<form method="post">
    <h1>Update Password
        <?php
        $user = mysqli_execute_query( $connection, "SELECT Name, password FROM user where id = " . $_COOKIE['user_id'] )[0];
        echo $user["Name"];
        ?>
    </h1>
    <?php
    if ($_REQUEST['password'] != $_REQUEST['password2']) { echo '<p style="color:red;">Passwords Dont match</p>';
    }
    ?>
    <label>Password</label>
    <input id='passwordBox' name="password">
    <em id="password_error" style="visibility: hidden">(must be 5 charicters long and contain a number)</em>
    <Script>
        password = document.getElementById('passwordBox').value
        if (password.length < 5) {
            document.getElementById('password_error').style.visibility = "visible";
        }
    </Script>
    <label>Confirm Password</label><input name="password2">
    <input type="submit" value="Update">
</form>
</html>
<?php
mysqli_close($connection); ?>
