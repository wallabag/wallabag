<?php

namespace Wallabag\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Wallabag\Entity\FeedItem;
use Wallabag\Entity\FeedUrl;
use Wallabag\Entity\User;

/**
 * @extends ServiceEntityRepository<FeedItem>
 */
class FeedItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeedItem::class);
    }

    /** @return list<string> */
    public function findKnownHashes(FeedUrl $feed): array
    {
        $results = $this->createQueryBuilder('item')
            ->select('item.urlHash AS urlHash')
            ->where('item.feed = :feed')->setParameter('feed', $feed)
            ->getQuery()
            ->getScalarResult();

        return array_column($results, 'urlHash');
    }

    /** @return list<FeedItem> */
    public function findPending(FeedUrl $feed): array
    {
        return $this->findBy(['feed' => $feed, 'isTrashed' => false], [
            'publishedAt' => 'DESC',
            'id' => 'DESC',
        ]);
    }

    public function dismissPending(FeedUrl $feed): int
    {
        return (int) $this->createQueryBuilder('item')
            ->update()
            ->set('item.isTrashed', ':trashed')->setParameter('trashed', true)
            ->set('item.title', 'NULL')
            ->set('item.image', 'NULL')
            ->where('item.feed = :feed')->setParameter('feed', $feed)
            ->andWhere('item.isTrashed = false')
            ->getQuery()
            ->execute();
    }

    /** @return list<int> */
    public function findPendingFeedIds(User $user): array
    {
        $rows = $this->createQueryBuilder('item')
            ->select('DISTINCT IDENTITY(item.feed) AS feedId')
            ->join('item.feed', 'feed')
            ->where('feed.user = :user')->setParameter('user', $user)
            ->andWhere('item.isTrashed = false')
            ->getQuery()
            ->getScalarResult();

        return array_map('intval', array_column($rows, 'feedId'));
    }

    public function getPendingCountBuilder(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('item')
            ->select('COUNT(DISTINCT item.urlHash)')
            ->join('item.feed', 'feed')
            ->where('feed.user = :user')->setParameter('user', $user)
            ->andWhere('item.isTrashed = false');
    }

    /** @param list<string> $currentHashes */
    public function prune(FeedUrl $feed, array $currentHashes, int $kept): int
    {
        $cutoff = $this->createQueryBuilder('item')
            ->select('item.id')
            ->where('item.feed = :feed')->setParameter('feed', $feed)
            ->orderBy('item.id', 'DESC')
            ->setFirstResult($kept)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $cutoff) {
            return 0;
        }

        $query = $this->createQueryBuilder('item')
            ->delete()
            ->where('item.feed = :feed')->setParameter('feed', $feed)
            ->andWhere('item.id <= :cutoff')->setParameter('cutoff', $cutoff['id']);

        if ([] !== $currentHashes) {
            $query->andWhere('item.urlHash NOT IN (:current)')->setParameter('current', $currentHashes);
        }

        return (int) $query->getQuery()->execute();
    }
}
