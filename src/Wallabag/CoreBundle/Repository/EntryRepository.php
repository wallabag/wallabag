<?php

namespace Wallabag\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class EntryRepository extends EntityRepository
{
    /**
     * Retrieves unread entries for a user.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    public function findUnreadByUser($userId)
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.user', 'u')
            ->where('e.isArchived = false')
            ->andWhere('u.id =:userId')->setParameter('userId', $userId)
            ->orderBy('e.id', 'desc');
    }

    /**
     * Retrieves read entries for a user.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    public function findArchiveByUser($userId)
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.user', 'u')
            ->where('e.isArchived = true')
            ->andWhere('u.id =:userId')->setParameter('userId', $userId)
            ->orderBy('e.id', 'desc');
    }

    /**
     * Retrieves starred entries for a user.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    public function findStarredByUser($userId)
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.user', 'u')
            ->where('e.isStarred = true')
            ->andWhere('u.id =:userId')->setParameter('userId', $userId)
            ->orderBy('e.id', 'desc');
    }

    /**
     * Find Entries.
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
            $qb->orderBy('e.id', $order);
        } elseif ('updated' === $sort) {
            $qb->orderBy('e.updatedAt', $order);
        }

        $pagerAdapter = new DoctrineORMAdapter($qb);

        return new Pagerfanta($pagerAdapter);
    }

    /**
     * Fetch an entry with a tag. Only used for tests.
     *
     * @return Entry
     */
    public function findOneWithTags($userId)
    {
        $qb = $this->createQueryBuilder('e')
            ->innerJoin('e.tags', 't')
            ->innerJoin('e.user', 'u')
            ->addSelect('t', 'u')
            ->where('e.user=:userId')->setParameter('userId', $userId)
        ;

        return $qb->getQuery()->getResult();
    }
}
