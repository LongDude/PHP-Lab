<?php

namespace src\Core;

use PDO;

use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    const host = 'db';

    public static function connect(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        try {
            self::$pdo = new PDO(
                "mysql:host=" . Database::host . ";dbname=" . $_ENV["MYSQL_DATABASE"],
                $_ENV['MYSQL_USER'],
                $_ENV['MYSQL_PASSWORD'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );

            self::$pdo->exec("set names utf8");
        } catch (PDOException $err) {
            die("Уронили базу данных: " . $err->getMessage());
        }
        return self::$pdo;
    }
}

