<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Client[] findByUser(int $userId)
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function findOneBy(array $criteria, array $orderBy = null)
    {
        if (!empty($criteria['id'])) {
            // cast client id to be an integer to avoid postgres error:
            // "invalid input syntax for integer"
            $criteria['id'] = (int) $criteria['id'];
        }

        return parent::findOneBy($criteria, $orderBy);
    }
}
