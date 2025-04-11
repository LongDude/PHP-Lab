<?php

namespace src\Models;

use src\Core\Database;
use src\Models\RequestBuilder;
use PDO;

class Order
{
    private PDO $pdo;

    const fields = array(
        'from_loc',
        'dest_loc',
        'distance',
        'orderedAt',
        'driver_id',
        'user_id',
    );

    public function __construct()
    {
        $this->pdo = Database::connect();
    }

    public function getListFiltered(array $filter, ?string $user_id=null, ?string $driver_id=null ): array
    {
        $builder = new RequestBuilder('SELECT u1.phone as phone, from_loc, dest_loc, distance, orderedAt, u1.name as user_name, u2.name as driver_name, t.name as tariff_name, o.price as price FROM orders o INNER JOIN tariffs t ON t.id = o.tariff_id JOIN users u1 on u1.id = o.user_id JOIN users u2 on u2.id = o.driver_id  where 1=1 ', $filter);
        [$stmt_raw, $prms] = $builder
            ->range('orderedAt')
            ->stringFuzzy("name", "driver_name")
            ->exact('tariff_id', 'o.tariff_id')
            ->build();
        
        if (isset($user_id)){
            $stmt_raw .= "and user_id = :user_id ";
            $prms[':user_id'] = $user_id;
        }

        if (isset($driver_id)){
            $stmt_raw .= "and driver_id = :driver_id ";
            $prms[':driver_id'] = $driver_id;
        }

        $stmt = $this->pdo->prepare($stmt_raw);
        $stmt->execute($prms);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvaliableRides(array $filter): array
    {
        $builder = new RequestBuilder('SELECT u.name as driver_name, u.id as driver_id, d.rating as rating, t.name as tariff_name, t.id as tariff_id, (t.base_price + GREATEST(0, :distance - t.base_dist) * t.dist_cost) as price FROM drivers d JOIN tariffs t on d.tariff_id = t.id JOIN users u on d.user_id = u.id where 1=1 ', $filter);
        [$stmt_raw, $prms] = $builder
            ->exact('tariff_id', 't.id')
            ->range('rating', 'd.rating')
            ->build();

        $prms['distance'] = $distance ?? 0;
        $stmt = $this->pdo->prepare($stmt_raw);
        $stmt->execute($prms);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addOrder(
        string $from_loc,
        string $dest_loc,
        float $distance,
        int $driver_id,
        int $user_id,
        ?string $orderedAt=null,
    ): bool {

        $sql = "INSERT INTO orders (from_loc, dest_loc, distance, driver_id, user_id";

        if (isset($orderedAt)) {
            $sql .= ", orderedAt";
        }
        $sql .= ") VALUES (:from_loc, :dest_loc, :distance, :driver_id, :user_id";

        if (isset($orderedAt)) {
            $sql .= ", :orderedAt";
        }
        $sql .= ')';

        $stmt = $this->pdo->prepare($sql);
        $params = array(
            ':from_loc' => $from_loc,
            ':dest_loc' => $dest_loc,
            ':distance' => $distance,
            ':driver_id' => $driver_id,
            ':user_id' => $user_id,
        );

        if (isset($orderedAt)){
            $params[':orderedAt'] = [$orderedAt];
        }

        $res = $stmt->execute($params);
        return $res;
    }

    public function importCsv(string $path): bool
    {
        $file = fopen($path, 'r');
        if ($file) {
            while (($row = fgetcsv($file, 1000, ',')) != false) {
                $this->addOrder(
                    $row[0],
                    $row[1],
                    $row[2],
                    $row[3],
                    $row[4],
                    $row[5],
                );
            }
            fclose($file);
            return true;
        }
        return false;
    }
}
?>