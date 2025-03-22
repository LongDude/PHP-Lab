<?php

namespace Src\Core;

use PDO;

use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    private static $host = 'db';
    private static $name = $_ENV['MYSQL_DATABASE'];
    private static $user = $_ENV['MYSQL_USER'];
    private static $pass = $_ENV['MYSQL_PASSWORD'];

    public static function connect(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        try {
            self::$pdo = new PDO(
                "mysql:host=" . self::$host . ";dbname=" . self::$name,
                self::$user,
                self::$pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            self::$pdo->exec("set names utf8");
        } catch (PDOException $err) {
            die("Уронили базу данных: " . $err->getMessage());
        }
        return self::$pdo;
    }
}

