<?php

namespace Wallabag\FederationBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class AccountRepository extends EntityRepository
{
    /**
     * @param $accountId
     * @return QueryBuilder
     */
    public function getBuilderForFollowingsByAccount($accountId)
    {
        return $this->createQueryBuilder('a')
            ->select('f.id, f.username')
            ->innerJoin('a.following', 'f')
            ->where('a.id = :accountId')->setParameter('accountId', $accountId)
            ;
    }

    /**
     * @param $accountId
     * @return QueryBuilder
     */
    public function getBuilderForFollowersByAccount($accountId)
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.followers', 'f')
            ->where('a.id = :accountId')->setParameter('accountId', $accountId)
            ;
    }

    /**
     * @param $username
     * @return QueryBuilder
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findAccountByUsername($username)
    {
        return $this->createQueryBuilder('a')
            ->where('a.username = :username')->setParameter('username', $username)
            ->andWhere('a.server = null')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
