<?php
require_once __DIR__ . '/Env.php';
Env::loadForHost(__DIR__ . '/..');

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct()
    {
        $this->host = Env::get('DB_HOST', '127.0.0.1');
        $this->db_name = Env::get('DB_NAME', 'fujiwarakenta_pen_app');
        $this->username = Env::get('DB_USER', 'root');
        $this->password = Env::get('DB_PASS', '');

        if ($this->host === 'localhost') {
            $this->host = '127.0.0.1';
        }
    }

    public function connect()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8mb4");
        } catch (PDOException $e) {
            // Log error instead of echoing in production, but for now echo is useful for debugging
            // In API context, this might break JSON if not caught.
            // Let the caller handle connection errors if possible, or return null.
            // For this simple app, we'll throw.
            throw $e;
        }

        return $this->conn;
    }
}
