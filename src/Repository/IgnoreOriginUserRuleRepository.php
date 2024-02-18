<?php

namespace Wallabag\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Wallabag\CoreBundle\Entity\IgnoreOriginUserRule;

class IgnoreOriginUserRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IgnoreOriginUserRule::class);
    }
}
