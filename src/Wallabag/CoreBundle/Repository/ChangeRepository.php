<?php

namespace Wallabag\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ChangeRepository extends EntityRepository
{
    /**
     * Used only in test case to get a tag for our entry.
     *
     * @param int $timestamp
     *
     * @return Tag
     */
    public function findChangesSinceDate($timestamp)
    {
        $date = new \DateTime();
        $date->setTimestamp($timestamp);

        return $this->createQueryBuilder('c')
            ->where('c.createdAt >= :timestamp')->setParameter('timestamp', $date)
            ->getQuery()
            ->getResult();
    }
}
