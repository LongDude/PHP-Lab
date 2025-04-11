<?php

namespace src\Models;
use PDOException;
use src\Core\Database;
use PDO;
use src\Models\RequestBuilder;

class User
{
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

    public function indetificate(string $email): mixed
    {
        $stmt = $this->pdo->prepare("SELECT * from users WHERE email=:email");
        $stmt->execute([":email" => $email]);
        return $stmt->fetch();
    }


    public function addUser(
        string $name,
        string $phone,
        string $email,
        string $password,
        string $role = 'client'
    ) {
        $stmt = $this->pdo->prepare("INSERT INTO users (name, phone, email, password, role) VALUES (:name, :phone, :email, :password, :role)");
        $res = $stmt->execute(array(
            ':name' => $name,
            ':phone' => $phone,
            ':email' => $email,
            ':password' => md5($password),
            ':role' => $role,
        ));
        return $res;
    }

    public function getUserId(
        string $email
    ){
        $stmt = $this->pdo->prepare("SELECT id from users where email=:email");
        $stmt -> execute([':email' => $email]);
        return $stmt->fetch()['id'];
    }

    public function updateUser(
        string $user_id,
        string $name,
        string $phone,
        string $email,
        string $password,
        string $role = 'client'
    ): bool {
        $stmt = $this->pdo->prepare("UPDATE users SET name=:name, phone=:phone, email=:email, password=:password, role=:role WHERE id=:user_id");
        $res = $stmt->execute(array(
            ':user_id' => $user_id,
            ':name' => $name,
            ':phone' => $phone,
            ':email' => $email,
            ':password' => md5($password),
            ':role' => $role,
        ));
        return $res;
    }

    public function importCsv(string $path): bool
    {
        $file = fopen($path, "r");
        if ($file) {
            while (($row = fgetcsv($file, 1000, ",")) != false) {
                $stmt = $this->pdo->prepare("INSERT INTO users (name, phone, email, password, role) VALUES (:name, :phone, :email, :password, :role)");
                $res = $stmt->execute(array(
                    ':name' => $row[0],
                    ':phone' => $row[1],
                    ':email' => $row[2],
                    ':password' => md5($row[3]),
                    ':role' => $row[4],
                ));
            }
            fclose($file);
            return true;
        }
        return false;
    }

    public function getListFiltered(array $filter): array
    {
        $builder = new RequestBuilder("SELECT name, phone, email, role FROM users where 1=1 ", $filter);
        [$stmt_raw, $prms] = $builder
            ->stringFuzzy("name")
            ->stringFuzzy("phone")
            ->stringFuzzy("email")
            ->build();

        $stmt = $this->pdo->prepare($stmt_raw);
        $stmt->execute($prms);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}