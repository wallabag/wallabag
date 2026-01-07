<?php

namespace Wallabag\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Wallabag\Entity\Annotation;

/**
 * AnnotationRepository.
 *
 * @method Annotation|null findOneById(int $id)
 */
class AnnotationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Annotation::class);
    }

    /**
     * Retrieves all annotations for a user.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    public function getBuilderForAllByUser($userId)
    {
        return $this
            ->getSortedQueryBuilderByUser($userId)
        ;
    }

    /**
     * Get annotation for this id.
     *
     * @param int $annotationId
     *
     * @return array
     */
    public function findAnnotationById($annotationId)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.id = :annotationId')->setParameter('annotationId', $annotationId)
            ->getQuery()
            ->getSingleResult()
        ;
    }

    /**
     * Find annotation by id and user.
     *
     * @param int $annotationId
     * @param int $userId
     *
     * @return Annotation
     */
    public function findOneByIdAndUserId($annotationId, $userId)
    {
        return $this->createQueryBuilder('a')
            ->where('a.id = :annotationId')->setParameter('annotationId', $annotationId)
            ->andWhere('a.user = :userId')->setParameter('userId', $userId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find annotations for entry id.
     *
     * @param int $entryId
     * @param int $userId
     *
     * @return array
     */
    public function findByEntryIdAndUserId($entryId, $userId)
    {
        return $this->createQueryBuilder('a')
            ->where('a.entry = :entryId')->setParameter('entryId', $entryId)
            ->andwhere('a.user = :userId')->setParameter('userId', $userId)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Find last annotation for a given entry id. Used only for tests.
     *
     * @param int $entryId
     *
     * @return Annotation|null
     */
    public function findLastAnnotationByUserId($entryId, $userId)
    {
        return $this->createQueryBuilder('a')
            ->where('a.entry = :entryId')->setParameter('entryId', $entryId)
            ->andwhere('a.user = :userId')->setParameter('userId', $userId)
            ->orderBy('a.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Used only in test case to get the right annotation associated to the right user.
     *
     * @param string $username
     *
     * @return Annotation
     */
    public function findOneByUsername($username)
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.user', 'u')
            ->where('u.username = :username')->setParameter('username', $username)
            ->orderBy('a.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * Remove all annotations for a user id.
     * Used when a user wants to reset all information.
     *
     * @param int $userId
     */
    public function removeAllByUserId($userId)
    {
        $this->getEntityManager()
            ->createQuery('DELETE FROM Wallabag\Entity\Annotation a WHERE a.user = :userId')
            ->setParameter('userId', $userId)
            ->execute();
    }

    /**
     * Find all annotations related to archived entries.
     */
    public function findAllArchivedEntriesByUser($userId)
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.entry', 'e')
            ->where('a.user = :userid')->setParameter(':userid', $userId)
            ->andWhere('e.isArchived = true')
            ->getQuery()
            ->getResult();
    }

    public function getCountBuilderByUser($userId = null)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.user = :userId')->setParameter('userId', $userId);
    }

    /**
     * Return a query builder to used by other getBuilderFor* method.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    private function getSortedQueryBuilderByUser($userId)
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.user', 'u')
            ->andWhere('u.id = :userId')->setParameter('userId', $userId)
            ->orderBy('a.id', 'desc')
        ;
    }
}
