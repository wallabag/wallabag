<?php

namespace WallabagBundle\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class EntriesRepository extends EntityRepository
{
    public function findUnreadByUser($userId, $firstResult, $maxResults = 12)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e')
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResults)
            ->where('e.isRead = 0')
            ->andWhere('e.userId =:userId')->setParameter('userId', $userId)
            ->getQuery();

        $pag = new Paginator($qb);

        return $pag;
    }

    public function findArchiveByUser($userId, $firstResult, $maxResults = 12)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e')
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResults)
            ->where('e.isRead = 1')
            ->andWhere('e.userId =:userId')->setParameter('userId', $userId)
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);

        return $qb;
    }

    public function findStarredByUser($userId, $firstResult, $maxResults = 12)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e')
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResults)
            ->where('e.isFav = 1')
            ->andWhere('e.userId =:userId')->setParameter('userId', $userId)
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);

        return $qb;
    }
}
