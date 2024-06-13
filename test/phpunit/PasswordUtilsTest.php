<?php declare(strict_types=1);

require_once(__DIR__ . '/../../PasswordUtils.php');

use PHPUnit\Framework\TestCase;


final class PasswordUtilsTest extends TestCase
{
    private $utils;

    protected function setUp(): void {
        $this->utils = new PasswordUtils();
        $this->utils->insertUserRow(1, 'PHPUnit', 'plaintext');
    }

    protected function tearDown(): void {
        $this->utils->deleteUserRow(1);
    }

    public function testFetchTemplate() {
        $html = $this->utils->fetchTemplate();
        $this->assertNotEmpty($html);

        $token = $_SESSION['token'];
        $this->assertStringContainsString($token, $html);
    }

    public function testGetName() {
        $name  = $this->utils->getName(1);
        $this->assertEquals('PHPUnit', $name, 'getName method failed');
    }

    public function testPassword() {
        $password  = $this->utils->getPassword(1);
        $this->assertTrue(password_verify('plaintext', $password));
    }
}
