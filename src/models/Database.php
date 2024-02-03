<?php
namespace Heisenberg\VasHouseAssessment\models;
class Database {
    //properities (db connection parameters)
    private $host = 'localhost';
    private $dbname = 'assignment';
    private $username = 'assignment_user';
    private $password = 'assignmentuser';
    private $pdo;

    //constructor method (establish database connection)
    public function __construct() {
        try{
            //PDO for MYSQL connection
            $this->pdo = new \PDO("mysql:host=$this->host;dbname=$this->dbname", $this->username, $this->password);
            //PDO error handling
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            //if successful connection
            // echo 'connected successfully';
        } catch(\PDOException $e) {
            //terminate, if exception occured
            die("connection failed" . $e->getMessage());
        }
    }

    //handle register in database
    public function getConnection() {
        return $this->pdo;
    }
}

?>