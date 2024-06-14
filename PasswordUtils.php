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
    private $_db;

    private $_defaultTemplate = './templates/bootstrap.html';

    function __construct()
    {
        $this->_db = $this->connect();
        $this->_createUserTable();
    }

    /**
     * Method to connect to database with values loaded from .env file
     *
     * @param string $host Database host
     * @param string $user Database username
     * @param string $password Database password
     * @param string $db Database name
     *
     * @return void
     */
    public function connect(
        $host = null, $user = null, $password = null, $db = null
    ): mysqli {
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

    /**
     * Closes the DB connection
     *
     * @return void
     */
    public function __destruct()
    {
        $this->_db->close();
    }

    /**
     * Fetch the HTML from a template
     *
     * @param string $path location of template file
     *
     * @return string
     */
    public function fetchTemplate($path = null) : string
    {
        $path = $path ?? $this->_defaultTemplate;
        $html = file_get_contents($path);

        $userId = isset($_COOKIE['user_id'])
            ? $_COOKIE['user_id']
            : 0;

        $message = '';
        if ($userId > 0) {
            $message = $this->_formSubmitted($userId);
        }

        $token = $this->_generateToken();

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

    /**
     * Force a cookie to be a certain ID
     *
     * @param int $id Row id for user
     *
     * @return void
     */
    public function testCookie($id = 1): void
    {
        setCookie('user_id', 1);
    }


    /**
     * Fetch the name by the user id
     *
     * @param int $id Row id for user
     *
     * @return string
     */
    public function getName($id = 0)
    {
        $sql = "SELECT Name from user where id = ?";
        $query = $this->_db->prepare($sql);
        $query->bind_param('i', $id);
        $query->execute();
        $result = $query->get_result();
        $user = $result->fetch_array(MYSQLI_ASSOC);
        return isset($user['Name']) ? $user['Name'] : '';
    }

    /**
     * Fetch the encrypted password for a user
     *
     * @param int $id Row id for user
     *
     * @return string
     */
    public function getPassword($id = 0)
    {
        $sql = "SELECT Password from user where id = ?";
        $query = $this->_db->prepare($sql);
        $query->bind_param('i', $id);
        $query->execute();
        $result = $query->get_result();
        $row = $result->fetch_array(MYSQLI_ASSOC);
        return isset($row['Password']) ? $row['Password'] : '';
    }

    /**
     * Inserts a user row
     *
     * @param int $id Row id for user
     * @param string $name name of the user
     * @param string $password password for the user
     *
     * @return void
     */
    public function insertUserRow(int $id, string $name, string $password): void
    {
        $sql = 'INSERT INTO user (id, Name) VALUES(?, ?) ' .
            'ON DUPLICATE KEY UPDATE Name = ?';
        $query = $this->_db->prepare($sql);
        $query->bind_param('iss', $id, $name, $name);
        $query->execute();
        $this->_setPassword($id, $password);
    }

    /**
     * Deletes a user row by ID
     *
     * @param int $id Row id for user
     *
     * @return void
     */
    public function deleteUserRow(int $id): void
    {
        $sql = "DELETE FROM user WHERE id = ?";
        $query = $this->_db->prepare($sql);
        $query->bind_param('i', $id);
        $query->execute();
    }

    /**
     * Handles the form being submitted
     *
     * @param int $userId Row id for user
     *
     * @return string
     */
    private function _formSubmitted($userId = 0): string
    {
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
            $this->_setPassword($userId, $password);
            return 'Password has been updated.';
        }
        return 'Something has failed';
    }

    /**
     * Hash a plain text password and store it in the table user
     *
     * @param string $userId   row id for user
     * @param string $password user password
     *
     * @return void
     */
    private function _setPassword(string $userId, string $password): void
    {
        // hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // store password
        $sql = "UPDATE user SET password = ? WHERE id = ?";
        $query = $this->_db->prepare($sql);
        $query->bind_param('si', $hashedPassword, $userId);
        $result = $query->execute();
    }

    /**
     * Create user table if it does not exist
     *
     * @return bool
     */
    private function _createUserTable(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS user (" .
            "id int NOT NULL PRIMARY KEY," .
            "Password varchar(255)," .
            "Name varchar(64))";
        $exists = $this->_db->query($sql);
        if (!$exists) {
            throw new Exception("Error creating user table");
        }
        return true;
    }

    /**
     * Generate a CSRF token to store in the session
     *
     * @return string
     */
    private function _generateToken()
    {
        $token = bin2hex(random_bytes(8));
        $_SESSION['token'] = $token;
        return $token;
    }


}
