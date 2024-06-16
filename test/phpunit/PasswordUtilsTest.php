<?php declare(strict_types=1);

require_once(__DIR__ . '/../../PasswordUtils.php');

use PHPUnit\Framework\TestCase;


final class PasswordUtilsTest extends TestCase
{
    private $utils;

    protected function setUp(): void {
        $host = getenv('DB_HOST');
        $user = getenv('DB_USER');
        $pw = getenv('DB_PASSWORD');
        $db = getenv('DB');

        if ($host && $user && $pw && $db) {
            $this->utils = new PasswordUtils($host, $user, $pw, $db);
        } else {
            $this->utils = new PasswordUtils();
        }
        $this->utils->insertUserRow(1, 'PHPUnit', 'plaintext');
    }

    protected function tearDown(): void {
        $this->utils->deleteUserRow(1);
        $this->utils->deleteUserRow(2);
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

        // test that the name is sanitised
        $userInputName = '<p>PHPUnit</br> possible user input</p>';
        $this->utils->insertUserRow(2, $userInputName, 'plaintext');
        $name  = $this->utils->getName(2);
        $this->assertNotEquals($userInputName, $name);

        $expectedName = 'PHPUnit possible user input';
        $this->assertEquals($expectedName, $name);
    }

    public function testDeleteUser() {
        $this->utils->deleteUserRow(1);
        $name  = $this->utils->getName(1);
        $this->assertEmpty($name);
    }

    public function testPassword() {
        $password  = $this->utils->getPassword(1);
        $this->assertTrue(password_verify('plaintext', $password));
    }
}
