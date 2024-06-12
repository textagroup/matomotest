<?php declare(strict_types=1);

require_once('PasswordUtils.php');

use PHPUnit\Framework\TestCase;


final class PasswordUtilsTest extends TestCase
{
    public function testFetchTemplate() {
        $passwordUtils = new PasswordUtils();
        $html = $passwordUtils->fetchTemplate();
        $this->assertNotEmpty($html);
    }
}
