<?php

namespace Wallabag\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter as DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Wallabag\Entity\EntryDeletion;

class EntryDeletionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntryDeletion::class);
    }

    /**
     * Find deletions for a specific user since a given date. The result is paginated.
     *
     * @param  int    $userId
     * @param  int    $since
     * @param  int    $page
     * @param  int    $perPage
     * @param  string $order
     */
    public function findEntryDeletions($userId, $since = 0, $order = 'asc'): Pagerfanta
    {
        $qb = $this->createQueryBuilder('de')
            ->where('de.user = :userId')->setParameter('userId', $userId)
            ->orderBy('de.deletedAt', $order);

        if ($since > 0) {
            $qb->andWhere('de.deletedAt >= :since')
                ->setParameter('since', new \DateTime(date('Y-m-d H:i:s', $since)));
        }

        $pagerAdapter = new DoctrineORMAdapter($qb, true, false);
        $pager = new Pagerfanta($pagerAdapter);

        return $pager;
    }

    public function countAllBefore(\DateTime $date): int
    {
        return $this->createQueryBuilder('de')
            ->select('COUNT(de.id)')
            ->where('de.deletedAt < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function deleteAllBefore(\DateTime $date)
    {
        $this->createQueryBuilder('de')
            ->delete()
            ->where('de.deletedAt < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
    }
}
