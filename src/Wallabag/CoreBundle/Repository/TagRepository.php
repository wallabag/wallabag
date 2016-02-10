<?php

namespace Wallabag\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class TagRepository extends EntityRepository
{
    /**
     * Return only the QueryBuilder to retrieve all tags for a given user.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    private function getQbForAllTags($userId)
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.entries', 'e')
            ->where('e.user = :userId')->setParameter('userId', $userId);
    }

    /**
     * Find Tags and return a Pager.
     *
     * @param int $userId
     *
     * @return Pagerfanta
     */
    public function findTags($userId)
    {
        $qb = $this->getQbForAllTags($userId);

        $pagerAdapter = new DoctrineORMAdapter($qb);

        return new Pagerfanta($pagerAdapter);
    }

    /**
     * Find Tags.
     *
     * @param int $userId
     *
     * @return array
     */
    public function findAllTags($userId)
    {
        return $this->getQbForAllTags($userId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Used only in test case to get a tag for our entry.
     *
     * @return Tag
     */
    public function findOnebyEntryAndLabel($entry, $label)
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
