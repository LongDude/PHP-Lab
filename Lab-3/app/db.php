<?php
    $host = "db";
    $dbname = $_ENV['MYSQL_DATABASE'];
    $user = $_ENV['MYSQL_USER'];
    $pass = $_ENV['MYSQL_PASSWORD'];

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8", 
        $user, 
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Error while connection " . $e->getMessage());
    }
?>