<?php
class DBConnection {
    private $host = "127.0.0.1";
    private $username = "root";
    private $password = "100403.dom";
    private $database = "gop_marketing_db";
    private $conn;

    public function __construct() {
        try {
            // Check if mysqli extension is loaded
            if (!extension_loaded('mysqli')) {
                throw new Exception("mysqli extension is not loaded");
            }

            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);

            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
        } catch (Exception $e) {
            die("Database Connection Error: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>