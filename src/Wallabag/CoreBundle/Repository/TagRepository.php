<?php

namespace Wallabag\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class TagRepository extends EntityRepository
{
    /**
     * Find Tags by user.
     *
     * @param int $userId
     *
     * @return array
     */
    public function findAllTagsByUser($userId)
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.entries', 'e')
            ->where('e.user = :userId')->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all Tags.
     *
     * @return array
     */
    public function findAllTags()
    {
        return $this->createQueryBuilder('t')
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
