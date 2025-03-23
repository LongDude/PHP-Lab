<?php

namespace Src\Models;
use PDOException;
use Src\Core\Database;
use PDO;
use Src\Models\RequestBuilder;

class Orders
{
    private PDO $pdo;
    public function __construct()
    {
        $this->pdo = Database::connect();
    }

    public function getList(): array|PDOException
    {
        $stmt = $this->pdo->prepare("SELECT * FROM orders");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getListFiltered(array $filter): array
    {
        $builder = new RequestBuilder("SELECT * FROM orders where 1=1 ", $filter);
        [$stmt_raw, $prms] = $builder
            ->range("deportedAt")
            ->exact("tariff_id")
            ->exact("driver_id")
            ->build();

        $stmt = $this->pdo->prepare($stmt_raw);
        $stmt->execute($prms);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addOrder(
        string $phone,
        string $from_loc,
        string $dest_loc,
        float $distance,
        float $price,
        string $orderedAt,
        int $driver_id,
        int $tariff_id,
    ): bool {
        $stmt = $this->pdo->prepare("INSERT INTO orders (phone, from_loc, dest_loc, distance, price, orderedAt, driver_id, tariff_id) VALUES (:phone, :from_loc, :dest_loc, :distance, :price, :orderedAt, :driver_id, :tariff_id)");
        $res = $stmt->execute(array(
            ':phone' => $phone,
            ':from_loc' => $from_loc,
            ':dest_loc' => $dest_loc,
            ':distance' => $distance,
            ':price' => $price,
            ':orderedAt' => $orderedAt,
            ':driver_id' => $driver_id,
            ':tariff_id' => $tariff_id,
        ));
        return $res;
    }

    public function importCsv(string $path): bool
    {
        $file = fopen($path, "r");
        if ($file) {
            while (($row = fgetcsv($file, 1000, ",")) != false) {
                $stmt = $this->pdo->prepare("INSERT INTO orders (phone, from_loc, dest_loc, distance, price, orderedAt, driver_id, tariff_id) VALUES (:phone, :from_loc, :dest_loc, :distance, :price, :orderedAt, :driver_id, :tariff_id)");
                $res = $stmt->execute(array(
                    ':phone' => $row[0],
                    ':from_loc' => $row[1],
                    ':dest_loc' => $row[2],
                    ':distance' => $row[3],
                    ':price' => $row[4],
                    ':orderedAt' => $row[5],
                    ':driver_id' => $row[6],
                    ':tariff_id' => $row[7],
                ));
            }
            fclose($file);
            return true;
        }
        return false;
    }
}
?>