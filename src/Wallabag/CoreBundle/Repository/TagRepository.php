<?php

namespace Wallabag\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class TagRepository extends EntityRepository
{
    public function findByEntries($entryId)
    {
        $qb = $this->createQueryBuilder('t')
            ->select('t')
            ->leftJoin('t.id', 'u')
            ->where('e.isStarred = true')
            ->andWhere('u.id =:userId')->setParameter('userId', $userId)
            ->orderBy('e.createdAt', 'desc')
            ->getQuery();

        $paginator = new Paginator($qb);

        return $paginator;
    }
}
