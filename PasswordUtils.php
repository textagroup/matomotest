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

    private $defaultTemplate = './templates/bootstrap.html';

    function __construct() {
        $this->db = $this->connect();
        $this->createUserTable();
    }

    /**
     * Method to connect to database with values loaded from .env file
     * TODO confirm it is only called once
     */
    public function connect($host = null, $user = null, $password = null, $db = null): mysqli {
        if (!$host || !$user || !$password || $db) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
            $dotenv->load();

            $keys = [
                'host' => 'DB_HOST',
                'user' => 'DB_USER',
                'password' => 'DB_PASSWORD',
                'db' => 'DB'
            ];

            foreach ($keys as $key => $value) {
                if (!isset($_ENV[$value])) {
                    throw new Exception("$key needs to exist in a .env file");
                }
                $$key = $_ENV[$value];
            }
        }
        return new mysqli($host, $user, $password, $db);
    }

    public function __destruct() {
        $this->db->close();
    }

    public function fetchTemplate($path = null) : string {
        $path = $path ?? $this->defaultTemplate;
        $html = file_get_contents($path);

        $userId = isset($_COOKIE['user_id'])
            ? $_COOKIE['user_id']
            : 0;

        $message = '';
        if ($userId > 0) {
            $message = $this->formSubmitted($userId);
        }

        $token = $this->generateToken();

        $search = [
            '#NAME#',
            '#MESSAGE#',
            '#TOKEN#',
        ];

        $replace = [
            $this->getName($userId),
            $message,
            $token,
        ];

        return str_replace($search, $replace, $html);
    }

    public function testCookie($id = 1) {
        setCookie('user_id', 1);
    }


    public function getName($id = 0) {
        $sql = "SELECT Name from user where id = ?";
        $query = $this->db->prepare($sql);
        $query->bind_param('i', $id);
        $query->execute();
        $result = $query->get_result();
        $user = $result->fetch_array(MYSQLI_ASSOC);
        return isset($user['Name']) ? $user['Name'] : '';
    }

    public function getPassword($id = 0) {
        $sql = "SELECT Password from user where id = ?";
        $query = $this->db->prepare($sql);
        $query->bind_param('i', $id);
        $query->execute();
        $result = $query->get_result();
        $row = $result->fetch_array(MYSQLI_ASSOC);
        return isset($row['Password']) ? $row['Password'] : '';
    }

    public function insertUserRow(int $id, string $name, string $password)
    {
        $sql = 'INSERT INTO user (id, Name) VALUES(?, ?) ' .
            'ON DUPLICATE KEY UPDATE Name = ?';
        $query = $this->db->prepare($sql);
        $query->bind_param('iss', $id, $name, $name);
        $query->execute();
        $this->setPassword($id, $password);
    }

    public function deleteUserRow(int $id)
    {
        $sql = "DELETE FROM user WHERE id = ?";
        $query = $this->db->prepare($sql);
        $query->bind_param('i', $id);
        $query->execute();
    }

    private function formSubmitted($userId = 0) {
        $password = $_REQUEST['password'];
        $password2 = $_REQUEST['password2'];
        $token = $_POST['token'];

        $sessionToken = $_SESSION['token'];

        if ($token != $sessionToken) {
            return false;
        }

        $error = null;

        if ($userId && ($password || $password2)) {
            if (empty($password) || empty($password2)) {
                $error = 'Both password fields need to filled in!';
            }
            if ($password != $password2) {
                $error = 'Passwords need to match!';
            }
            if (!preg_match('/\d/', $password)) {
                $error = 'Password needs to contain at least 1 digit!';
            }
            if (strlen($password) < 5) {
                $error = 'Password needs to be at least 5 characters';
            }

            // js validation should catch this before we get here
            if ($error) {
                return $error;
            }
            $this->setPassword($userId, $password);
            return 'Password has been updated.';
        }
        return 'Something has failed';
    }

    private function setPassword($userId, $password) {
        // hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // store password
        $sql = "UPDATE user SET password = ? WHERE id = ?";
        $query = $this->db->prepare($sql);
        $query->bind_param('si', $hashedPassword, $userId);
        $result = $query->execute();
    }

    // create db table
    private function createUserTable(): bool {
        $sql = "CREATE TABLE IF NOT EXISTS user (" .
            "id int NOT NULL PRIMARY KEY," .
            "Password varchar(255)," .
            "Name varchar(64))";
        $exists = $this->db->query($sql);
        if (!$exists) {
            throw new Exception("Error creating user table");
        }
        return true;
    }

    private function generateToken() {
        $token = bin2hex(random_bytes(8));
        $_SESSION['token'] = $token;
        return $token;
    }


}
