<?php

namespace Wallabag\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class TagRepository extends EntityRepository
{
    /**
     * Find Tags.
     *
     * @param int    $userId
     *
     * @return array
     */
    public function findTags($userId)
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.user =:userId')->setParameter('userId', $userId);

        $pagerAdapter = new DoctrineORMAdapter($qb);

        return new Pagerfanta($pagerAdapter);
    }
}
