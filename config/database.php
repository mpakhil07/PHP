<?php
class Database {
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        $this->host     = getenv("MYSQLHOST");
        $this->port     = getenv("MYSQLPORT");
        $this->db_name  = getenv("MYSQLDATABASE");
        $this->username = getenv("MYSQLUSER");
        $this->password = getenv("MYSQLPASSWORD");
    }

    public function getConnection() {
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

            return $this->conn;

        } catch (PDOException $exception) {
            error_log("DB ERROR: " . $exception->getMessage());
            die("Database connection failed.");
        }
    }
}
