<?php

namespace src\Models;
use PDOException;
use src\Core\Database;
use PDO;
use src\Models\RequestBuilder;

class Tariff
{
    private PDO $pdo;
    const fields = array(
        'name',
        'base_price',
        'base_dist',
        'dist_cost',
    );
    public function __construct()
    {
        $this->pdo = Database::connect();
    }

    public function getList(): array
    {
        $stmt = $this->pdo->prepare("SELECT name, base_price, base_dist, dist_cost FROM tariffs");
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
        float $dist_cost,
    ): bool {
        $stmt = $this->pdo->prepare("INSERT INTO tariffs (name, base_price, base_dist, dist_cost) VALUES (:name, :base_price, :base_dist, :dist_cost)");
        $res = $stmt->execute(array(
            ':name' => $name,
            ':base_price' => $base_price,
            ':base_dist' => $base_dist,
            ':dist_cost' => $dist_cost,
        ));
        return $res;
    }

    public function importCsv(string $path): bool
    {
        $file = fopen($path, "r");
        if ($file) {
            while (($row = fgetcsv($file, 1000, ",")) != false) {
                $stmt = $this->pdo->prepare("INSERT INTO tariffs (name, base_price, base_dist, dist_cost) VALUES (:name, :base_price, :base_dist, :dist_cost)");
                $res = $stmt->execute(array(
                    ':name' => $row[0],
                    ':base_price' => $row[1],
                    ':base_dist' => $row[2],
                    ':dist_cost' => $row[3],
                ));
            }
            fclose($file);
            return true;
        }
        return false;
    }
}
?>