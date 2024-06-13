<?php

session_start();

require_once('PasswordUtils.php');

// The constructor can also accept the db details directly
//$passwordUtils = new PasswordUtils('localhost','db_user', 'Pa55word!', 'my_site' );
$passwordUtils = new PasswordUtils();

$passwordUtils->insertUserRow(1, 'User', 'weakpassword');

$passwordUtils->testCookie();

$html = $passwordUtils->fetchTemplate();

echo $html;
