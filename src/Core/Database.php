<?php

namespace App\Core;

use Exception;
use PDO;
use PDOException;

class Database
{
    private $pdo;


    public function __construct($host, $dbName, $userName, $password)
    {
        try {
            $this->pdo = new PDO('mysql:host=' . $host . ';dbname=' . $dbName, $userName, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception('Failed to connect to database: ' . $e->getMessage());
        }
    }

    public function table($tableName)
    {
        return new Builder($this->pdo, $tableName);
    }
}
