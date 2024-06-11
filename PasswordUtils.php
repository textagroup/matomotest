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

    private $error;

    function __construct() {
        $this->db = $this->connect();
        $this->createUserTable();
    }

    /**
     * Method to connect to database with values loaded from .env file
     * TODO confirm it is only called once
     */
    private function connect(): mysqli {
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
                $this->error = "A error has occured";
                throw new Exception("$key needs to exist in a .env file");
            }
            $$key = $_ENV[$value];
        }
        return new mysqli($host, $user, $password, $db);
    }

    private function __destruct() {
        $this->db->close();
    }

    // create db table
    private function createUserTable(): bool {
        $sql = "CREATE TABLE IF NOT EXISTS user (" .
            "id int," .
            "password varchar(255)," .
            "salt varchar(64)," .
            "Name varchar(64))";
        $exists = $this->db->query($sql);
        if (!$exists) {
            $this->error = "User table error";
            throw new Exception("Error creating user table");
        }
    }
}
