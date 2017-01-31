<?php

namespace Wallabag\GroupBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Wallabag\UserBundle\Entity\User;

class GroupRepository extends EntityRepository
{
    /**
     * Return a query builder to used by other getBuilderFor* method.
     *
     * @return QueryBuilder
     */
    public function getBuilder()
    {
        return $this->createQueryBuilder('g');
    }

    public function findPublicGroups()
    {
        return $this->getBuilder()
            ->where('g.acceptSystem < 10');
    }

    public function findGroupsByUser(User $user)
    {
        return $this->getBuilder()
            ->join('Wallabag\GroupBundle\Entity\UserGroup', 'u', 'WITH', 'u.group = g.id')
            ->where('u.user = :user')->setParameter(':user', $user->getId());
    }
}
