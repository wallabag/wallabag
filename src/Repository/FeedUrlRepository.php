<?php

namespace Wallabag\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Wallabag\Entity\FeedUrl;

/**
 * @extends ServiceEntityRepository<FeedUrl>
 */
class FeedUrlRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeedUrl::class);
    }

    /** @return iterable<FeedUrl> */
    public function iterateAll(): iterable
    {
        return $this->createQueryBuilder('feed')->orderBy('feed.id', 'ASC')->getQuery()->toIterable();
    }
}
