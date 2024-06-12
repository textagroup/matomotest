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

    private $error = '';

    private $defaultTemplate = './templates/form.html';

    function __construct() {
        $this->db = $this->connect();
        $this->createUserTable();
    }

    /**
     * Method to connect to database with values loaded from .env file
     * TODO confirm it is only called once
     */
    public function connect(): mysqli {
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
                $this->error = "A error has occured";
                throw new Exception("$key needs to exist in a .env file");
            }
            $$key = $_ENV[$value];
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

        if ($userId > 0) {
            $this->formSubmitted($userId);
        }

        $token = $this->generateToken();

        $search = [
            '#NAME#',
            '#ERROR#',
            '#TOKEN#',
        ];

        $replace = [
            $this->getName($userId),
            $this->error,
            $token,
        ];

        return str_replace($search, $replace, $html);
    }

    public function testCookie($id = 1) {
        setCookie('user_id', 1);
    }


    private function generateToken() {
        $token = bin2hex(random_bytes(8));
        $_SESSION['token'] = $token;
        return $token;
    }

    private function getName($id = 0) {
        $sql = "SELECT Name from user where id = ?";
        $query = $this->db->prepare($sql);
        $query->bind_param('i', $id);
        $query->execute();
        $result = $query->get_result();
        $user = $result->fetch_array(MYSQLI_ASSOC);
        return isset($user['Name']) ? $user['Name'] : '';
    }

    private function formSubmitted($userId = 0) {
        $password = $_REQUEST['password'];
        $password2 = $_REQUEST['password2'];
        $token = $_POST['token'];

        $sessionToken = $_SESSION['token'];

        if ($token != $sessionToken) {
            return false;
        }

        if ($userId && ($password || $password2)) {
            if (empty($password) || empty($password2)) {
                $this->error = 'Both password fields need to filled in!';
                return;
            }
            if ($password != $password2) {
                $this->error = 'Passwords need to match!';
                return;
            }
            if (!preg_match('/\d/', $password)) {
                $this->error = 'Password needs to contain at least 1 digit!';
                return;
            }
            if (strlen($password) < 5) {
                $this->error = 'Password needs to be at least 5 characters';
                return;
            }

            $this->setPassword($userId, $password);
        }
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
            "id int," .
            "Password varchar(255)," .
            "Name varchar(64))";
        $exists = $this->db->query($sql);
        if (!$exists) {
            $this->error = "User table error";
            throw new Exception("Error creating user table");
        }
        return true;
    }

}
