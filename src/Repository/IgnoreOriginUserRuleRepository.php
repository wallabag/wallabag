<?php

namespace App\Repository;

use App\Entity\IgnoreOriginUserRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class IgnoreOriginUserRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IgnoreOriginUserRule::class);
    }
}
