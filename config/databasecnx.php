<?php
class ConnectData {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "location";
    public $connection;

    public function __construct() {
        $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);

        if ($this->connection->connect_error) {
            die("Database connection failed: " . $this->connection->connect_error);
        } else {
            echo "3la slamtak"; // Success message
        }
    }

    public function getConnection() {
        return $this->connection;
    }
}

// Instantiate the database connection
$db = (new ConnectData())->getConnection();
?>
