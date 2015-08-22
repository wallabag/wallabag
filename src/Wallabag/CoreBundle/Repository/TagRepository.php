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
     * @param int $userId
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

    /**
     * Find a tag by its label and its owner.
     *
     * @param string $label
     * @param int    $userId
     *
     * @return Tag|null
     */
    public function findOneByLabelAndUserId($label, $userId)
    {
        return $this->createQueryBuilder('t')
            ->where('t.label = :label')->setParameter('label', $label)
            ->andWhere('t.user = :user_id')->setParameter('user_id', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
