<?php

namespace Wallabag\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class EntryRepository extends EntityRepository
{
    /**
     * Retrieves unread entries for a user
     *
     * @param int $userId
     * @param int $firstResult
     * @param int $maxResults
     *
     * @return Paginator
     */
    public function findUnreadByUser($userId, $firstResult, $maxResults = 12)
    {
        $qb = $this->createQueryBuilder('e')
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResults)
            ->leftJoin('e.user', 'u')
            ->where('e.isArchived = false')
            ->andWhere('u.id =:userId')->setParameter('userId', $userId)
            ->orderBy('e.createdAt', 'desc')
            ->getQuery();

        $paginator = new Paginator($qb);

        return $paginator;
    }

    /**
     * Retrieves read entries for a user
     *
     * @param int $userId
     * @param int $firstResult
     * @param int $maxResults
     *
     * @return Paginator
     */
    public function findArchiveByUser($userId, $firstResult, $maxResults = 12)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e')
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResults)
            ->leftJoin('e.user', 'u')
            ->where('e.isArchived = true')
            ->andWhere('u.id =:userId')->setParameter('userId', $userId)
            ->orderBy('e.createdAt', 'desc')
            ->getQuery();

        $paginator = new Paginator($qb);

        return $paginator;
    }

    /**
     * Retrieves starred entries for a user
     *
     * @param int $userId
     * @param int $firstResult
     * @param int $maxResults
     *
     * @return Paginator
     */
    public function findStarredByUser($userId, $firstResult, $maxResults = 12)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e')
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResults)
            ->leftJoin('e.user', 'u')
            ->where('e.isStarred = true')
            ->andWhere('u.id =:userId')->setParameter('userId', $userId)
            ->orderBy('e.createdAt', 'desc')
            ->getQuery();

        $paginator = new Paginator($qb);

        return $paginator;
    }

    /**
     * Find Entries
     *
     * @param int    $userId
     * @param bool   $isArchived
     * @param bool   $isStarred
     * @param string $sort
     * @param string $order
     *
     * @return array
     */
    public function findEntries($userId, $isArchived = null, $isStarred = null, $sort = 'created', $order = 'ASC')
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.user =:userId')->setParameter('userId', $userId);

        if (null !== $isArchived) {
            $qb->andWhere('e.isArchived =:isArchived')->setParameter('isArchived', (bool) $isArchived);
        }

        if (null !== $isStarred) {
            $qb->andWhere('e.isStarred =:isStarred')->setParameter('isStarred', (bool) $isStarred);
        }

        if ('created' === $sort) {
            $qb->orderBy('e.createdAt', $order);
        } elseif ('updated' === $sort) {
            $qb->orderBy('e.updatedAt', $order);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}
