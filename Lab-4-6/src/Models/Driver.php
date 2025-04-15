<?php

namespace src\Models;
use PDOException;
use src\Core\Database;
use PDO;
use src\Models\RequestBuilder;

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
        $stmt = $this->pdo->prepare("SELECT u.name as name, u.phone, u.email, intership, car_license, car_brand, t.name as tariff_name FROM drivers d INNER JOIN tariffs t on t.id = d.tariff_id INNER JOIN users u on u.id = d.user_id");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEntries(): array {
        $stmt = $this->pdo->prepare("SELECT d.id as id, u.name as name FROM drivers d JOIN users u on u.id = d.user_id");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getListFiltered(array $filter): array
    {
        $builder = new RequestBuilder("SELECT u.name as name, u.phone as phone, u.email as email, d.intership as intership, d.car_license as car_license, d.car_brand as car_brand, t.name as tariff_name FROM drivers d INNER JOIN tariffs t on t.id = d.tariff_id INNER JOIN users u on u.id = d.user_id where 1=1 ", $filter);
        [$stmt_raw, $prms] = $builder
            ->stringFuzzy("name", "u.name")
            ->stringFuzzy("phone", "u.phone")
            ->stringFuzzy("email", "u.email")
            ->exact("car_license")
            ->exact("tariff_id")
            ->build();

        $stmt = $this->pdo->prepare($stmt_raw);
        $stmt->execute($prms);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addDriver(
        string $user_id,
        int     $intership,
        string  $car_license,
        string  $car_brand,
        int     $tariff_id
    ): bool {
        $stmt = $this->pdo->prepare("INSERT INTO drivers (user_id, intership, car_license, car_brand, tariff_id) VALUES (:user_id, :intership, :car_license, :car_brand, :tariff_id)");
        $res = $stmt->execute(array(
            ':user_id' => $user_id,
            ':intership' => $intership,
            ':car_license' => $car_license,
            ':car_brand' => $car_brand,
            ':tariff_id' => $tariff_id,
        ));
        return $res;
    }

    public function editDriver(
        string  $driver_id,
        int     $intership,
        string  $car_license,
        string  $car_brand,
        int     $tariff_id
    ): bool {
        $stmt = $this->pdo->prepare("UPDATE drivers SET intership:=intership, car_license:=car_license, car_brand:=car_brand, tariff_id:=tariff_id where id=:driver_id");
        $res = $stmt->execute(array(
            ':driver_id' => $driver_id,
            ':intership' => $intership,
            ':car_license' => $car_license,
            ':car_brand' => $car_brand,
            ':tariff_id' => $tariff_id,
        ));
        return $res;
    }

    public function getDriver(string $user_id){
        $stmt = $this->pdo->prepare("SELECT * from drivers where user_id=:user_id");
        $stmt -> execute([':user_id' => $user_id]);
        return $stmt->fetch();
    }

    public function importCsv(string $path): bool
    {
        $file = fopen($path, "r");
        if ($file) {
            while (($row = fgetcsv($file, 1000, ",")) != false) {
                $stmt = $this->pdo->prepare("INSERT INTO drivers (user_id, intership, car_license, car_brand, tariff_id) VALUES (:user_id, :intership, :car_license, :car_brand, :tariff_id)");
                $res = $stmt->execute(array(
                    ':user_id' => $row[0],
                    ':intership' => $row[1],
                    ':car_license' => $row[2],
                    ':car_brand' => $row[3],
                    ':tariff_id' => $row[4],
                ));
            }
            fclose($file);
            return true;
        }
        return false;
    }
}
?>