<?php

session_start();

require_once('PasswordUtils.php');

$passwordUtils = new PasswordUtils();

$passwordUtils->insertUserRow(1, 'User', 'weakpassword');

$passwordUtils->testCookie();

$html = $passwordUtils->fetchTemplate();

echo $html;
