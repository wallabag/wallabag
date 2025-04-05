<?php

namespace Wallabag\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Wallabag\Entity\Tag;

/**
 * @method Tag|null findOneByLabel(string $label)
 * @method Tag|null findOneBySlug(string $slug)
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly string $tablePrefix,
    ) {
        parent::__construct($registry, Tag::class);
    }

    /**
     * Count all tags per user.
     *
     * @param int $userId
     * @param int $cacheLifeTime Duration of the cache for this query
     *
     * @return int
     */
    public function countAllTags($userId, $cacheLifeTime = null)
    {
        $query = $this->createQueryBuilder('t')
            ->select('t.slug')
            ->leftJoin('t.entries', 'e')
            ->where('e.user = :userId')->setParameter('userId', $userId)
            ->groupBy('t.slug')
            ->getQuery();

        if (null !== $cacheLifeTime) {
            $query->useQueryCache(true);
            $query->enableResultCache($cacheLifeTime);
        }

        return \count($query->getArrayResult());
    }

    /**
     * Find all tags per user.
     * Instead of just left joined on the Entry table, we select only id and group by id to avoid tag multiplication in results.
     * Once we have all tags id, we can safely request them one by one.
     * This'll still be fastest than the previous query.
     *
     * @param int $userId
     *
     * @return array
     */
    public function findAllTags($userId)
    {
        $ids = $this->getQueryBuilderByUser($userId)
            ->select('t.id')
            ->getQuery()
            ->getArrayResult();

        $tags = [];
        foreach ($ids as $id) {
            $tags[] = $this->find($id);
        }

        return $tags;
    }

    /**
     * Find all tags (flat) per user with nb entries.
     *
     * @param int $userId
     *
     * @return array
     */
    public function findAllFlatTagsWithNbEntries($userId)
    {
        return $this->getQueryBuilderByUser($userId)
            ->select('t.id, t.label, t.slug, count(e.id) as nbEntries')
            ->distinct(true)
            ->orderBy('t.label')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Find all tags per user with nb entries.
     *
     * @param int $userId
     *
     * @return array<array{tag: Tag, nbEntries: int}>
     */
    public function findAllTagsWithNbEntries($userId)
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(Tag::class, 't');
        $rsm->addEntityResult(Tag::class, 't', 'tag');
        $rsm->addScalarResult('nbEntries', 'nbEntries', 'integer');

        $sql = <<<SQL
            SELECT DISTINCT {$rsm->generateSelectClause()}, count(e.id) as nbEntries
            FROM {$this->tablePrefix}tag t
            LEFT JOIN {$this->tablePrefix}entry_tag et ON et.tag_id = t.id
            JOIN {$this->tablePrefix}entry e ON e.id = et.entry_id
            WHERE e.user_id = :userId
            GROUP BY t.id
            ORDER BY t.label
        SQL;

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('userId', $userId);

        return $query->getResult();
    }

    public function findByLabelsAndUser($labels, $userId)
    {
        $qb = $this->getQueryBuilderByUser($userId)
            ->select('t.id');

        $ids = $qb->andWhere($qb->expr()->in('t.label', $labels))
            ->getQuery()
            ->getArrayResult();

        $tags = [];
        foreach ($ids as $id) {
            $tags[] = $this->find($id);
        }

        return $tags;
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

    public function findForArchivedArticlesByUser($userId)
    {
        $ids = $this->getQueryBuilderByUser($userId)
            ->select('t.id')
            ->andWhere('e.isArchived = true')
            ->getQuery()
            ->getArrayResult();

        $tags = [];
        foreach ($ids as $id) {
            $tags[] = $this->find($id);
        }

        return $tags;
    }

    /**
     * Retrieve a sorted list of tags used by a user.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    private function getQueryBuilderByUser($userId)
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.entries', 'e')
            ->where('e.user = :userId')->setParameter('userId', $userId)
            ->groupBy('t.id')
            ->orderBy('t.slug');
    }
}
