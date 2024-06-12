<?php

session_start();

require_once('PasswordUtils.php');

$passwordUtils = new PasswordUtils();

$passwordUtils->testCookie();

$html = $passwordUtils->fetchTemplate();

echo $html;
