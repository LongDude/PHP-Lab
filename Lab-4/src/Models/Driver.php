<?php

namespace Src\Models;
use PDOException;
use Src\Core\Database;
use PDO;
use Src\Models\RequestBuilder;

class Driver
{
    private PDO $pdo;
 
    const fields = array(
        'name',
        'phone',
        'email',
        'intership',
        'car_license',
        'car_brand',
        'tariff_id',
    );

    public function __construct()
    {
        $this->pdo = Database::connect();
    }

    public function getList(): array
    {
        $stmt = $this->pdo->prepare("SELECT d.name, d.phone, d.email, d.intership, d.car_license, d.car_brand, t.name FROM drivers d INNER JOIN tariffs t on t.id == d.tariff_id");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEntries(): array {
        $stmt = $this->pdo->prepare("SELECT id, name FROM drivers");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getListFiltered(array $filter): array
    {
        $builder = new RequestBuilder("SELECT d.name, d.phone, d.email, d.intership, d.car_license, d.car_brand, t.name FROM drivers d where 1=1 ", $filter);

        [$stmt_raw, $prms] = $builder
            ->stringFuzzy("name")
            ->stringFuzzy("phone")
            ->stringFuzzy("email")
            ->range("intership")
            ->exact("car_license")
            ->stringFuzzy("car_brand")
            ->exact("tariff_id")
            ->build();
        $stmt_raw .= "INNER JOIN tariffs t on t.id == d.tariff_id";
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