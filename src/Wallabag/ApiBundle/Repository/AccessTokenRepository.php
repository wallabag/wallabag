<?php

namespace Wallabag\ApiBundle\Repository;

use Doctrine\ORM\EntityRepository;


class AccessTokenRepository extends EntityRepository
{
    public function findAppsByUser($userId)
    {
        $qb = $this->createQueryBuilder('a')
            ->innerJoin('a.client', 'c')
            ->addSelect('c')
            ->where('a.user =:userId')->setParameter('userId', $userId);
        return $qb->getQuery()->getResult();
    }
}
