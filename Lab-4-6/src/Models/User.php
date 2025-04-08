<?php

namespace src\Models;
use PDOException;
use src\Core\Database;
use PDO;
use src\Models\RequestBuilder;

class User{
    private PDO $pdo;
    const fields = array(
        'name',
        'phone',
        'email',
        'password',
        'role',
    );
    public function __construct()
    {
        $this->pdo = Database::connect();
    }
}