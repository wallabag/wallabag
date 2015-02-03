<?php

namespace Wallabag\CoreBundle\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Wallabag\CoreBundle\Entity\Entries;

class EntriesRepository extends EntityRepository
{
    /**
     * Retrieves unread entries for a user
     *
     * @param $userId
     * @param $firstResult
     * @param  int       $maxResults
     * @return Paginator
     */
    public function findUnreadByUser($userId, $firstResult, $maxResults = 12)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e')
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResults)
            ->where('e.isRead = 0')
            ->andWhere('e.userId =:userId')->setParameter('userId', $userId)
            ->getQuery();

        $paginator = new Paginator($qb);

        return $paginator;
    }

    /**
     * Retrieves read entries for a user
     *
     * @param $userId
     * @param $firstResult
     * @param  int       $maxResults
     * @return Paginator
     */
    public function findArchiveByUser($userId, $firstResult, $maxResults = 12)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e')
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResults)
            ->where('e.isRead = 1')
            ->andWhere('e.userId =:userId')->setParameter('userId', $userId)
            ->getQuery();

        $paginator = new Paginator($qb);

        return $paginator;
    }

    /**
     * Retrieves starred entries for a user
     *
     * @param $userId
     * @param $firstResult
     * @param  int       $maxResults
     * @return Paginator
     */
    public function findStarredByUser($userId, $firstResult, $maxResults = 12)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e')
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResults)
            ->where('e.isFav = 1')
            ->andWhere('e.userId =:userId')->setParameter('userId', $userId)
            ->getQuery();

        $paginator = new Paginator($qb);

        return $paginator;
    }

    public function findEntries($userId, $isArchived, $isStarred, $isDeleted, $sort, $order)
    {
        //TODO tous les paramètres ne sont pas utilisés, à corriger
        $qb = $this->createQueryBuilder('e')
            ->select('e')
            ->where('e.userId =:userId')->setParameter('userId', $userId);
        if ($isStarred == '1' || $isStarred == '0') {
            $qb = $qb->andWhere('e.isFav =:isStarred')->setParameter('isStarred', $isStarred);
        }
        if ($isArchived == '1' || $isArchived == '0') {
            $qb = $qb->andWhere('e.isRead =:isArchived')->setParameter('isArchived', $isArchived);
        }

        $qb = $qb->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);

        return $qb;
    }
}
