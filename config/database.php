<?php
class Database {
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        /**
         * If Railway environment variables exist → use them
         * Otherwise → fallback to localhost (XAMPP)
         */
        $this->host     = getenv("MYSQLHOST") ?: "localhost";
        $this->port     = getenv("MYSQLPORT") ?: "3306";
        $this->db_name  = getenv("MYSQLDATABASE") ?: "internship_portal";
        $this->username = getenv("MYSQLUSER") ?: "root";
        $this->password = getenv("MYSQLPASSWORD") ?: "Shadowfight9072@";
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";

            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );

        } catch (PDOException $exception) {
            error_log("DB Connection Error: " . $exception->getMessage());
            die("Database connection failed.");
        }

        return $this->conn;
    }
}
?>
