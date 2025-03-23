<?php

namespace Src\Models;
use PDOException;
use Src\Core\Database;
use PDO;
use Src\Models\RequestBuilder;

class Tariff
{
    private PDO $pdo;
    const fields = array(
        'name',
        'base_price',
        'base_dist',
        'base_time',
        'dist_cost',
        'time_cost',
    );
    public function __construct()
    {
        $this->pdo = Database::connect();
    }

    public function getList(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tariffs");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEntries(): array {
        $stmt = $this->pdo->prepare("SELECT id, name FROM tariffs");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getListFiltered(array $filter): array
    {
        $builder = new RequestBuilder("SELECT * FROM tariffs where 1=1 ", $filter);
        [$stmt_raw, $prms] = $builder
            ->stringFuzzy("name")
            ->range("base_price")
            ->build();

        $stmt = $this->pdo->prepare($stmt_raw);
        $stmt->execute($prms);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addTariff(
        string $name,
        float $base_price,
        float $base_dist,
        float $base_time,
        float $dist_cost,
        float $time_cost
    ): bool {
        $stmt = $this->pdo->prepare("INSERT INTO tariffs (name, base_price, base_dist, base_time, dist_cost, time_cost) VALUES (:name, :base_price, :base_dist, :base_time, :dist_cost, :time_cost)");
        $res = $stmt->execute(array(
            ':name' => $name,
            ':base_price' => $base_price,
            ':base_dist' => $base_dist,
            ':base_time' => $base_time,
            ':dist_cost' => $dist_cost,
            ':time_cost' => $time_cost,
        ));
        return $res;
    }

    public function importCsv(string $path): bool
    {
        $file = fopen($path, "r");
        if ($file) {
            while (($row = fgetcsv($file, 1000, ",")) != false) {
                $stmt = $this->pdo->prepare("INSERT INTO tariffs (name, base_price, base_dist, base_time, dist_cost, time_cost) VALUES (:name, :base_price, :base_dist, :base_time, :dist_cost, :time_cost)");
                $res = $stmt->execute(array(
                    ':name' => $row[0],
                    ':base_price' => $row[1],
                    ':base_dist' => $row[2],
                    ':base_time' => $row[3],
                    ':dist_cost' => $row[4],
                    ':time_cost' => $row[5],
                ));
            }
            fclose($file);
            return true;
        }
        return false;
    }
}
?>