<?php

namespace Wallabag\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class TagRepository extends EntityRepository
{
    /**
     * Find all tags per user.
     *
     * @param int $userId
     * @param int $cacheLifeTime Duration of the cache for this query
     *
     * @return array
     */
    public function findAllTags($userId, $cacheLifeTime = null)
    {
        $query = $this->createQueryBuilder('t')
            ->select('t')
            ->leftJoin('t.entries', 'e')
            ->where('e.user = :userId')->setParameter('userId', $userId)
            ->groupBy('t.slug')
            ->getQuery();

        if (null !== $cacheLifeTime) {
            $query->useQueryCache(true);
            $query->useResultCache(true);
            $query->setResultCacheLifetime($cacheLifeTime);
        }

        return $query->getArrayResult();
    }

    /**
     * Find all tags with associated entries per user.
     *
     * @param int $userId
     *
     * @return array
     */
    public function findAllTagsWithEntries($userId)
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.entries', 'e')
            ->where('e.user = :userId')->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Used only in test case to get a tag for our entry.
     *
     * @return Tag
     */
    public function findOneByEntryAndTagLabel($entry, $label)
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.entries', 'e')
            ->where('e.id = :entryId')->setParameter('entryId', $entry->getId())
            ->andWhere('t.label = :label')->setParameter('label', $label)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }
}
