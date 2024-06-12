<?php

require_once('PasswordUtils.php');

$passwordUtils = new PasswordUtils();

$passwordUtils->testCookie();

$html = $passwordUtils->fetchTemplate();

echo $html;
