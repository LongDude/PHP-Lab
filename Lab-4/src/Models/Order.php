<?php

namespace src\Models;
use PDOException;
use src\Core\Database;
use PDO;
use src\Models\RequestBuilder;

class Order
{
    private PDO $pdo;

    const fields = array(
        'phone',
        'from_loc',
        'dest_loc',
        'distance',
        'orderedAt',
        'driver_id',
        'tariff_id',
    );

    public function __construct()
    {
        $this->pdo = Database::connect();
    }

    public function getList(): array
    {
        $stmt = $this->pdo->prepare("SELECT o.phone as phone, from_loc, dest_loc, distance, orderedAt, d.name as driver_name, t.name as tariff_name FROM orders o INNER JOIN tariffs t ON t.id = o.tariff_id INNER JOIN drivers d on d.id = o.driver_id");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getListFiltered(array $filter): array
    {
        $builder = new RequestBuilder("SELECT o.phone as phone, from_loc, dest_loc, distance, orderedAt, d.name as driver_name, t.name as tariff_name FROM orders o INNER JOIN tariffs t ON t.id = o.tariff_id INNER JOIN drivers d on d.id = o.driver_id where 1=1 ", $filter);
        [$stmt_raw, $prms] = $builder
            ->range("orderedAt")
            ->exact("tariff_id", "o.tariff_id")
            ->exact("driver_id", "o.driver_id")
            ->build();
        $stmt = $this->pdo->prepare($stmt_raw);
        $stmt->execute($prms);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvaliableRides(array $filter, $distance = null): array
    {
        $builder = new RequestBuilder("SELECT d.name as driver_name, d.id as driver_id, d.rating as rating, t.name as tariff_name, t.id as tariff_id, (t.base_price + GREATEST(0, :distance - t.base_dist) * t.dist_cost) as price FROM drivers d JOIN tariffs t on d.tariff_id = t.id where 1=1 ", $filter);
        [$stmt_raw, $prms] = $builder
        ->exact("tariff_id", "t.id")
        ->range("rating", "d.rating")
        ->build();

        $prms['distance'] = $distance ?? 0;
        $stmt = $this->pdo->prepare($stmt_raw);
        $stmt->execute($prms);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function addOrder(
        string $phone,
        string $from_loc,
        string $dest_loc,
        float $distance,
        int $driver_id
    ): bool {
        $stmt = $this->pdo->prepare("INSERT INTO orders (phone, from_loc, dest_loc, distance, driver_id) VALUES (:phone, :from_loc, :dest_loc, :distance, :driver_id)");

        $res = $stmt->execute(array(
            ':phone' => $phone,
            ':from_loc' => $from_loc,
            ':dest_loc' => $dest_loc,
            ':distance' => $distance,
            ':driver_id' => $driver_id
        ));
        return $res;
    }

    public function importCsv(string $path): bool
    {
        $file = fopen($path, "r");
        if ($file) {
            while (($row = fgetcsv($file, 1000, ",")) != false) {
                $stmt = $this->pdo->prepare("INSERT INTO orders (phone, from_loc, dest_loc, distance, orderedAt, driver_id, tariff_id) VALUES (:phone, :from_loc, :dest_loc, :distance, :orderedAt, :driver_id, :tariff_id)");
                $res = $stmt->execute(array(
                    ':phone' => $row[0],
                    ':from_loc' => $row[1],
                    ':dest_loc' => $row[2],
                    ':distance' => $row[3],
                    ':orderedAt' => $row[4],
                    ':driver_id' => $row[5],
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