<?php

namespace src\Repository;

use Doctrine\ORM\EntityRepository;
use src\Entities\Tariff;
use Exception;
class TariffRepository extends EntityRepository {
    public function getFilteredList(array $filters = []): array{
        $qb = $this->createQueryBuilder('t')
                ->select('t.name', 't.base_price', 't.base_dist', 't.dist_cost');
        $qfb = new QueryFilters($qb, $filters);
        $qfb->like('name', 't.name')
            ->range('base_price', 't.base_price');
        return $qb->getQuery()->getResult();
    }

    public function addTariff(
        string $name,
        float $base_price,
        float $base_dist,
        float $dist_cost,
    ): Tariff{
        $tariff = new Tariff();
        $tariff
        ->setName($name)
        ->setBasePrice($base_price)
        ->setBaseDist($base_dist)
        ->setDistCost($dist_cost);

        $this->getEntityManager()->persist($tariff);
        $this->getEntityManager()->flush();
        return $tariff;
    } 

    public function updateTariff(
        Tariff $tariff,
        ?string $name = null,
        ?float $base_price = null,
        ?float $base_dist = null,
        ?float $dist_cost = null,
    ): Tariff {
        if($name !== null) {
            $tariff->setName($name);
        }
        if($base_price !== null) {
            $tariff->setBasePrice($base_price);
        }
        if($base_dist !== null) {
            $tariff->setBaseDist($base_dist);
        }
        if($dist_cost !== null) {
            $tariff->setDistCost($dist_cost);
        }
        $this->getEntityManager()->flush();
        return $tariff;
    }

    public function importCsv(string $filePath): bool
    {
        $em = $this->getEntityManager();
        if (($handle = fopen($filePath, 'r')) !== false) {
            // Skip header row if exists
            fgetcsv($handle);
            
            $em->getConnection()->beginTransaction();
            
            try {
                while (($data = fgetcsv($handle))) {
                    $tariff = $this->findOneBy(['name' => $data[0]]);
                    if (!$tariff){
                        $tariff = new Tariff();
                        $em->persist($tariff);
                    }
                    $tariff
                    ->setName($data[0])
                    ->setBasePrice($data[1])
                    ->setBaseDist($data[2])
                    ->setDistCost($data[3]);
                }
                
                $em->flush();
                $em->getConnection()->commit();
            } catch (Exception $e) {
                $em->getConnection()->rollBack();
                throw $e;
            }
            
            fclose($handle);
        }
        return true;
    }
}