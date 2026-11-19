<?php

namespace Wallabag\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Wallabag\Entity\FeedItem;
use Wallabag\Entity\FeedUrl;

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
            ->where('item.feed = :feed')->setParameter('feed', $feed)
            ->andWhere('item.isTrashed = false')
            ->getQuery()
            ->execute();
    }
}
