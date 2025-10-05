<?php

namespace Wallabag\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Wallabag\Entity\User;

/**
 * @method User|null findOneById(int $id)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Find a user by its username and Feed token.
     *
     * @param string $username
     * @param string $feedToken
     *
     * @return User|null
     */
    public function findOneByUsernameAndFeedtoken($username, $feedToken)
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.config', 'c')
            ->where('c.feedToken = :feed_token')->setParameter('feed_token', $feedToken)
            ->andWhere('u.username = :username')->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find a user by its username.
     *
     * @param string $username
     *
     * @return User
     */
    public function findOneByUserName($username)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.username = :username')->setParameter('username', $username)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * Count how many users are enabled.
     *
     * @return int
     */
    public function getSumEnabledUsers()
    {
        return $this->createQueryBuilder('u')
            ->select('count(u)')
            ->andWhere('u.enabled = true')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count how many users are existing.
     *
     * @return int
     */
    public function getSumUsers()
    {
        return $this->createQueryBuilder('u')
            ->select('count(u)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Retrieves users filtered with a search term.
     *
     * @param string $term
     *
     * @return QueryBuilder
     */
    public function getQueryBuilderForSearch($term)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('lower(u.username) LIKE lower(:term) OR lower(u.email) LIKE lower(:term) OR lower(u.name) LIKE lower(:term)')->setParameter('term', '%' . $term . '%');
    }
}
