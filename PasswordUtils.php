<?php

require __DIR__ . '/vendor/autoload.php';

/**
 * Password utility class
 * SQL to create user
 * CREATE USER 'db_user'@'localhost' IDENTIFIED BY 'Pa55word!';
 * SQL to grant access to DB
 * GRANT ALL ON my_site.* TO 'db_user'@'localhost' WITH GRANT OPTION;
 */
class PasswordUtils
{
    private $db;

    function __construct() {
        $this->db = $this->connect();
    }

    /**
     * Method to connect to database with values loaded from .env file
     * TODO confirm it is only called once
     */
    private function connect() {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        $keys = [
            'host' => 'HOST',
            'user' => 'USER',
            'password' => 'PASSWORD',
            'db' => 'DB'
        ];

        foreach ($keys as $key => $value) {
            if (!isset($_ENV[$value])) {
                echo "A error has occure";
                throw new Exception("$key needs to exist in a .env file");
            }
            $$key = $_ENV[$value];
        }
        return new mysqli($host, $user, $password, $db);
    }

    private function __destruct() {
        $this->db->close();
    }

    // check if table exists
    // create db table
}
