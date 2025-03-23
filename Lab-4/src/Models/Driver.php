<?php

namespace Src\Models;
use PDOException;
use Src\Core\Database;
use PDO;
use Src\Models\RequestBuilder;

class Driver
{
    private PDO $pdo;
    public function __construct()
    {
        $this->pdo = Database::connect();
    }

    public function getList(): array|PDOException
    {
        $stmt = $this->pdo->prepare("SELECT * FROM drivers");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getListFiltered(array $filter): array
    {
        $builder = new RequestBuilder("SELECT * FROM drivers where 1=1 ", $filter);

        [$stmt_raw, $prms] = $builder
            ->stringFuzzy("name")
            ->stringFuzzy("phone")
            ->stringFuzzy("email")
            ->range("intership")
            ->exact("car_license")
            ->stringFuzzy("car_brand")
            ->exact("tariff_id")
            ->build();

        $stmt = $this->pdo->prepare($stmt_raw);
        $stmt->execute($prms);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addDriver(
        string  $name,
        string  $phone,
        string  $email,
        int     $intership,
        string  $car_license,
        string  $car_brand,
        int     $tariff_id
    ): bool {
        $stmt = $this->pdo->prepare("INSERT INTO drivers (name, phone, email, intership, car_license, car_brand, tariff_id) VALUES (:name, :phone, :email, :intership, :car_license, :car_brand, :tariff_id)");
        $res = $stmt->execute(array(
            ':name' => $name,
            ':phone' => $phone,
            ':email' => $email,
            ':intership' => $intership,
            ':car_license' => $car_license,
            ':car_brand' => $car_brand,
            ':tariff_id' => $tariff_id,
        ));
        return $res;
    }

    public function importCsv(string $path): bool
    {
        $file = fopen($path, "r");
        if ($file) {
            while (($row = fgetcsv($file, 1000, ",")) != false) {
                $stmt = $this->pdo->prepare("INSERT INTO drivers (name, phone, email, intership, car_license, car_brand, tariff_id) VALUES (:name, :phone, :email, :intership, :car_license, :car_brand, :tariff_id)");
                $res = $stmt->execute(array(
                    ':name' => $row[0],
                    ':phone' => $row[1],
                    ':email' => $row[2],
                    ':intership' => $row[3],
                    ':car_license' => $row[4],
                    ':car_brand' => $row[5],
                    ':tariff_id' => $row[6],
                ));
            }
            fclose($file);
            return true;
        }
        return false;
    }
}
?>