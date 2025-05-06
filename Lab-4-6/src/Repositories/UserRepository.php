<?php

namespace src\Repository;

use Doctrine\ORM\EntityRepository;
use src\Entities\User;
use Exception;

class UserRepository extends EntityRepository {
    public function getFilteredList(array $filters = []): array {
        $qb = $this->createQueryBuilder('u')
        ->select('name', 'phone', 'email', 'role');

        $qfb = new QueryFilters($qb, $filters);
        $qfb->like('name')
            ->like('phone')
            ->like('email');
        return $qb->getQuery()->getResult();
    }

    public function addUser(
        string $name,
        string $phone,
        string $email,
        string $password,
        string $role = 'client'
    ): User {
        $user = new User();
        $user->setName($name)
             ->setPhone($phone)
             ->setEmail($email)
             ->setPassword($password)
             ->setRole($role);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
        return $user;
    }

    public function updateUser(
        User $user,
        ?string $name = null,
        ?string $phone = null,
        ?string $email = null,
        ?string $password = null,
        ?string $role = null
    ): User {
        if ($name !== null) {
            $user->setName($name);
        }
        if ($phone !== null) {
            $user->setPhone($phone);
        }
        if ($email !== null) {
            $user->setEmail($email);
        }
        if ($password !== null) {
            $user->setPassword($password);
        }
        if ($role !== null) {
            $user->setRole($role);
        }
        $this->getEntityManager()->flush();
        return $user;
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
                    // Assuming CSV format: name,phone,email,intership,car_license,car_brand,tariff_id

                    $user = $this->findOneBy(['phone' => $data[1]]) ??
                            $this->findOneBy(['email' => $data[2]]) ??
                            new User();

                    $user->setName($data[0])
                         ->setPhone($data[1])
                         ->setEmail($data[2])
                         ->setPassword($data[3])
                         ->setRole($data[4]);
                    
                    $em->persist($user);
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