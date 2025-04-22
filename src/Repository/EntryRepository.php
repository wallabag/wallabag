<?php

namespace Wallabag\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter as DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Wallabag\Entity\Entry;
use Wallabag\Entity\Tag;
use Wallabag\Helper\UrlHasher;

/**
 * @method Entry[]    findById(int[] $id)
 * @method Entry|null findOneByUser(int $userId)
 */
class EntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entry::class);
    }

    /**
     * Retrieves all entries for a user.
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
     * Retrieves all entries count for a user.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    public function getCountBuilderForAllByUser($userId)
    {
        return $this
            ->getQueryBuilderByUser($userId)
        ;
    }

    /**
     * Retrieves unread entries for a user.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    public function getBuilderForUnreadByUser($userId)
    {
        return $this
            ->getSortedQueryBuilderByUser($userId)
            ->andWhere('e.isArchived = false')
        ;
    }

    /**
     * Retrieves unread entries count for a user.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    public function getCountBuilderForUnreadByUser($userId)
    {
        return $this
            ->getQueryBuilderByUser($userId)
            ->andWhere('e.isArchived = false')
        ;
    }

    /**
     * Retrieves entries with the same domain.
     *
     * @param int $userId
     * @param int $entryId
     *
     * @return QueryBuilder
     */
    public function getBuilderForSameDomainByUser($userId, $entryId)
    {
        $queryBuilder = $this->createQueryBuilder('e');

        return $this
            ->getSortedQueryBuilderByUser($userId)
            ->andWhere('e.id <> :entryId')->setParameter('entryId', $entryId)
            ->andWhere(
                $queryBuilder->expr()->in(
                    'e.domainName',
                    $this
                        ->createQueryBuilder('e2')
                        ->select('e2.domainName')
                        ->where('e2.id = :entryId')->setParameter('entryId', $entryId)
                        ->getDQL()
                )
            );
    }

    /**
     * Retrieves read entries for a user.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    public function getBuilderForArchiveByUser($userId)
    {
        return $this
            ->getSortedQueryBuilderByUser($userId, 'archivedAt', 'desc')
            ->andWhere('e.isArchived = true')
        ;
    }

    /**
     * Retrieves read entries count for a user.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    public function getCountBuilderForArchiveByUser($userId)
    {
        return $this
            ->getQueryBuilderByUser($userId)
            ->andWhere('e.isArchived = true')
        ;
    }

    /**
     * Retrieves starred entries for a user.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    public function getBuilderForStarredByUser($userId)
    {
        return $this
            ->getSortedQueryBuilderByUser($userId, 'starredAt', 'desc')
            ->andWhere('e.isStarred = true')
        ;
    }

    /**
     * Retrieves starred entries count for a user.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    public function getCountBuilderForStarredByUser($userId)
    {
        return $this
            ->getQueryBuilderByUser($userId)
            ->andWhere('e.isStarred = true')
        ;
    }

    /**
     * Retrieves entries filtered with a search term for a user.
     *
     * @param int                                          $userId
     * @param string                                       $term
     * @param 'starred'|'unread'|'homepage'|'archive'|null $currentRoute
     *
     * @return QueryBuilder
     */
    public function getBuilderForSearchByUser($userId, $term, $currentRoute)
    {
        $qb = $this
            ->getSortedQueryBuilderByUser($userId);

        if ('starred' === $currentRoute) {
            $qb->andWhere('e.isStarred = true');
        } elseif ('unread' === $currentRoute || 'homepage' === $currentRoute) {
            $qb->andWhere('e.isArchived = false');
        } elseif ('archive' === $currentRoute) {
            $qb->andWhere('e.isArchived = true');
        }

        // We lower() all parts here because PostgreSQL 'LIKE' verb is case-sensitive
        $qb
            ->andWhere('lower(e.content) LIKE lower(:term) OR lower(e.title) LIKE lower(:term) OR lower(e.url) LIKE lower(:term) OR lower(a.text) LIKE lower(:term)')
            ->setParameter('term', '%' . $term . '%')
            ->leftJoin('e.tags', 't')
            ->leftJoin('e.annotations', 'a')
            ->groupBy('e.id');

        return $qb;
    }

    /**
     * Retrieve a sorted list of untagged entries for a user.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    public function getBuilderForUntaggedByUser($userId)
    {
        return $this->sortQueryBuilder($this->getRawBuilderForUntaggedByUser($userId));
    }

    /**
     * Retrieve entries with annotations for a user.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    public function getBuilderForAnnotationsByUser($userId)
    {
        return $this
            ->getSortedQueryBuilderByUser($userId)
            ->innerJoin('e.annotations', 'a')
        ;
    }

    /**
     * Retrieve untagged entries for a user.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    public function getRawBuilderForUntaggedByUser($userId)
    {
        return $this->getQueryBuilderByUser($userId)
            ->leftJoin('e.tags', 't')
            ->andWhere('t.id is null');
    }

    /**
     * Retrieve the number of untagged entries for a user.
     *
     * @param int $userId
     *
     * @return int
     */
    public function countUntaggedEntriesByUser($userId)
    {
        return (int) $this->getRawBuilderForUntaggedByUser($userId)
            ->select('count(e.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find Entries.
     *
     * @param int    $userId
     * @param bool   $isArchived
     * @param bool   $isStarred
     * @param bool   $isPublic
     * @param string $sort
     * @param string $order
     * @param int    $since
     * @param string $tags
     * @param string $detail      'metadata' or 'full'. Include content field if 'full'
     * @param string $domainName
     * @param int    $httpStatus
     * @param bool   $isNotParsed
     *
     * @todo Breaking change: replace default detail=full by detail=metadata in a future version
     *
     * @return Pagerfanta
     */
    public function findEntries($userId, $isArchived = null, $isStarred = null, $isPublic = null, $sort = 'created', $order = 'asc', $since = 0, $tags = '', $detail = 'full', $domainName = '', $isNotParsed = null, $httpStatus = null)
    {
        if (!\in_array(strtolower($detail), ['full', 'metadata'], true)) {
            throw new \Exception('Detail "' . $detail . '" parameter is wrong, allowed: full or metadata');
        }

        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.tags', 't')
            ->where('e.user = :userId')->setParameter('userId', $userId);

        if ('metadata' === $detail) {
            $fieldNames = $this->getClassMetadata()->getFieldNames();
            $fields = array_filter($fieldNames, fn ($k) => 'content' !== $k);
            $qb->select(\sprintf('partial e.{%s}', implode(',', $fields)));
        }

        if (null !== $isArchived) {
            $qb->andWhere('e.isArchived = :isArchived')->setParameter('isArchived', (bool) $isArchived);
        }

        if (null !== $isStarred) {
            $qb->andWhere('e.isStarred = :isStarred')->setParameter('isStarred', (bool) $isStarred);
        }

        if (null !== $isPublic) {
            $qb->andWhere('e.uid IS ' . (true === $isPublic ? 'NOT' : '') . ' NULL');
        }

        if (null !== $isNotParsed) {
            $qb->andWhere('e.isNotParsed = :isNotParsed')->setParameter('isNotParsed', (bool) $isNotParsed);
        }

        if ($since > 0) {
            $qb->andWhere('e.updatedAt > :since')->setParameter('since', new \DateTime(date('Y-m-d H:i:s', $since)));
        }

        if (\is_string($tags) && '' !== $tags) {
            foreach (explode(',', $tags) as $i => $tag) {
                $entryAlias = 'e' . $i;
                $tagAlias = 't' . $i;

                // Complex queries to ensure multiple tags are associated to an entry
                // https://stackoverflow.com/a/6638146/569101
                $qb->andWhere($qb->expr()->in(
                    'e.id',
                    $this->createQueryBuilder($entryAlias)
                        ->select($entryAlias . '.id')
                        ->leftJoin($entryAlias . '.tags', $tagAlias)
                        ->where($tagAlias . '.label = :label' . $i)
                        ->getDQL()
                ));

                // bound parameter to the main query builder
                $qb->setParameter('label' . $i, $tag);
            }
        }

        if (\is_int($httpStatus)) {
            $qb->andWhere('e.httpStatus = :httpStatus')->setParameter('httpStatus', $httpStatus);
        }

        if (\is_string($domainName) && '' !== $domainName) {
            $qb->andWhere('e.domainName = :domainName')->setParameter('domainName', $domainName);
        }

        if (!\in_array(strtolower($order), ['asc', 'desc'], true)) {
            throw new \Exception('Order "' . $order . '" parameter is wrong, allowed: asc or desc');
        }

        if ('created' === $sort) {
            $qb->orderBy('e.id', $order);
        } elseif ('updated' === $sort) {
            $qb->orderBy('e.updatedAt', $order);
        } elseif ('archived' === $sort) {
            $qb->orderBy('e.archivedAt', $order);
        }

        $pagerAdapter = new DoctrineORMAdapter($qb, true, false);

        return new Pagerfanta($pagerAdapter);
    }

    /**
     * Fetch an entry with a tag. Only used for tests.
     *
     * @param int $userId
     *
     * @return array
     */
    public function findOneWithTags($userId)
    {
        $qb = $this->createQueryBuilder('e')
            ->innerJoin('e.tags', 't')
            ->innerJoin('e.user', 'u')
            ->addSelect('t', 'u')
            ->where('e.user = :userId')->setParameter('userId', $userId)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Find distinct language for a given user.
     * Used to build the filter language list.
     *
     * @param int $userId User id
     *
     * @return array
     */
    public function findDistinctLanguageByUser($userId)
    {
        $results = $this->createQueryBuilder('e')
            ->select('e.language')
            ->where('e.user = :userId')->setParameter('userId', $userId)
            ->andWhere('e.language IS NOT NULL')
            ->groupBy('e.language')
            ->orderBy('e.language', ' ASC')
            ->getQuery()
            ->getResult();

        $languages = [];
        foreach ($results as $result) {
            $languages[$result['language']] = $result['language'];
        }

        return $languages;
    }

    /**
     * Used only in test case to get the right entry associated to the right user.
     *
     * @param string $username
     *
     * @return Entry
     */
    public function findOneByUsernameAndNotArchived($username)
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.user', 'u')
            ->where('u.username = :username')->setParameter('username', $username)
            ->andWhere('e.isArchived = false')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * Remove a tag from all user entries.
     *
     * We need to loop on each entry attached to the given tag to remove it, since Doctrine doesn't know EntryTag entity because it's a ManyToMany relation.
     * It could be faster with one query but I don't know how to retrieve the table name `entry_tag` which can have a prefix:
     *
     * DELETE et FROM entry_tag et WHERE et.entry_id IN ( SELECT e.id FROM entry e WHERE e.user_id = :userId ) AND et.tag_id = :tagId
     *
     * @param int $userId
     */
    public function removeTag($userId, Tag $tag)
    {
        $entries = $this->getSortedQueryBuilderByUser($userId)
            ->innerJoin('e.tags', 't')
            ->andWhere('t.id = :tagId')->setParameter('tagId', $tag->getId())
            ->getQuery()
            ->getResult();

        foreach ($entries as $entry) {
            $entry->removeTag($tag);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * Remove tags from all user entries.
     *
     * @param int        $userId
     * @param array<Tag> $tags
     */
    public function removeTags($userId, $tags)
    {
        foreach ($tags as $tag) {
            $this->removeTag($userId, $tag);
        }
    }

    /**
     * Find all entries that are attached to a give tag id.
     *
     * @param int $userId
     * @param int $tagId
     *
     * @return array
     */
    public function findAllByTagId($userId, $tagId, $sort = 'createdAt')
    {
        return $this->getSortedQueryBuilderByUser($userId, $sort)
            ->innerJoin('e.tags', 't')
            ->andWhere('t.id = :tagId')->setParameter('tagId', $tagId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find an entry by its url and its owner.
     * If it exists, return the entry otherwise return false.
     *
     * @param string $url
     * @param int    $userId
     *
     * @return Entry|false
     */
    public function findByUrlAndUserId($url, $userId)
    {
        return $this->findByHashedUrlAndUserId(
            UrlHasher::hashUrl($url),
            $userId
        );
    }

    /**
     * Find all entries which have an empty value for hash.
     *
     * @return Entry[]
     */
    public function findByEmptyHashedUrlAndUserId(int $userId)
    {
        return $this->createQueryBuilder('e')
            ->where('e.hashedUrl = :empty')->setParameter('empty', '')
            ->orWhere('e.hashedUrl is null')
            ->andWhere('e.user = :user_id')->setParameter('user_id', $userId)
            ->andWhere('e.url is not null')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find an entry by its hashed url and its owner.
     * If it exists, return the entry otherwise return false.
     *
     * @param string $hashedUrl Url hashed using sha1
     * @param int    $userId
     *
     * @return Entry|false
     */
    public function findByHashedUrlAndUserId($hashedUrl, $userId)
    {
        // try first using hashed_url (to use the database index)
        $res = $this->createQueryBuilder('e')
            ->where('e.hashedUrl = :hashed_url')->setParameter('hashed_url', $hashedUrl)
            ->andWhere('e.user = :user_id')->setParameter('user_id', $userId)
            ->getQuery()
            ->getResult();

        if (\count($res)) {
            return current($res);
        }

        // then try using hashed_given_url (to use the database index)
        $res = $this->createQueryBuilder('e')
            ->where('e.hashedGivenUrl = :hashed_given_url')->setParameter('hashed_given_url', $hashedUrl)
            ->andWhere('e.user = :user_id')->setParameter('user_id', $userId)
            ->getQuery()
            ->getResult();

        if (\count($res)) {
            return current($res);
        }

        return false;
    }

    public function findByUserIdAndBatchHashedUrls($userId, $hashedUrls)
    {
        $qb = $this->createQueryBuilder('e')->select(['e.id', 'e.hashedUrl', 'e.hashedGivenUrl']);
        $res = $qb->where('e.user = :user_id')->setParameter('user_id', $userId)
                    ->andWhere(
                        $qb->expr()->orX(
                            $qb->expr()->in('e.hashedUrl', $hashedUrls),
                            $qb->expr()->in('e.hashedGivenUrl', $hashedUrls)
                        )
                    )
                    ->getQuery()
                    ->getResult();

        return $res;
    }

    /**
     * Count all entries for a user.
     *
     * @param int $userId
     *
     * @return int
     */
    public function countAllEntriesByUser($userId)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('count(e)')
            ->where('e.user = :userId')->setParameter('userId', $userId)
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Remove all entries for a user id.
     * Used when a user wants to reset all information.
     *
     * @param int $userId
     */
    public function removeAllByUserId($userId)
    {
        $this->getEntityManager()
            ->createQuery('DELETE FROM Wallabag\Entity\Entry e WHERE e.user = :userId')
            ->setParameter('userId', $userId)
            ->execute();
    }

    public function removeArchivedByUserId($userId)
    {
        $this->getEntityManager()
            ->createQuery('DELETE FROM Wallabag\Entity\Entry e WHERE e.user = :userId AND e.isArchived = TRUE')
            ->setParameter('userId', $userId)
            ->execute();
    }

    /**
     * Get id and url from all entries
     * Used for the clean-duplicates command.
     */
    public function findAllEntriesIdAndUrlByUserId($userId)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.id, e.url')
            ->where('e.user = :userid')->setParameter(':userid', $userId);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param int $userId
     *
     * @return array
     */
    public function findAllEntriesIdByUserId($userId = null)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.id');

        if (null !== $userId) {
            $qb->where('e.user = :userid')->setParameter(':userid', $userId);
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param int $userId
     *
     * @return array
     */
    public function findAllEntriesIdByUserIdAndNotParsed($userId = null)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.id')
            ->where('e.isNotParsed = true');

        if (null !== $userId) {
            $qb->where('e.user = :userid')->setParameter(':userid', $userId);
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param int $userId
     *
     * @return array
     */
    public function findEmptyEntriesIdByUserId($userId = null)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.id');

        if (null !== $userId) {
            $qb->where('e.user = :userid AND e.content IS NULL')->setParameter(':userid', $userId);
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Find all entries by url and owner.
     *
     * @param string $url
     * @param int    $userId
     *
     * @return array
     */
    public function findAllByUrlAndUserId($url, $userId)
    {
        return $this->createQueryBuilder('e')
            ->where('e.url = :url')->setParameter('url', urldecode($url))
            ->andWhere('e.user = :user_id')->setParameter('user_id', $userId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns a random entry, filtering by status.
     *
     * @param int    $userId
     * @param string $type   Can be unread, archive, starred, etc
     *
     * @throws NoResultException
     *
     * @return Entry
     */
    public function getRandomEntry($userId, $type = '')
    {
        $qb = $this->getQueryBuilderByUser($userId)
            ->select('e.id');

        switch ($type) {
            case 'unread':
                $qb->andWhere('e.isArchived = false');
                break;
            case 'archive':
                $qb->andWhere('e.isArchived = true');
                break;
            case 'starred':
                $qb->andWhere('e.isStarred = true');
                break;
            case 'untagged':
                $qb->leftJoin('e.tags', 't');
                $qb->andWhere('t.id is null');
                break;
            case 'annotated':
                $qb->leftJoin('e.annotations', 'a');
                $qb->andWhere('a.id is not null');
                break;
        }

        $ids = $qb->getQuery()->getArrayResult();

        if (empty($ids)) {
            throw new NoResultException();
        }

        // random select one in the list
        $randomId = $ids[mt_rand(0, \count($ids) - 1)]['id'];

        return $this->find($randomId);
    }

    /**
     * Return a query builder to be used by other getBuilderFor* method.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    private function getQueryBuilderByUser($userId)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.user = :userId')->setParameter('userId', $userId);
    }

    /**
     * Return a sorted query builder to be used by other getBuilderFor* method.
     *
     * @param int    $userId
     * @param string $sortBy
     * @param string $direction
     *
     * @return QueryBuilder
     */
    private function getSortedQueryBuilderByUser($userId, $sortBy = 'createdAt', $direction = 'desc')
    {
        return $this->sortQueryBuilder($this->getQueryBuilderByUser($userId), $sortBy, $direction);
    }

    /**
     * Return the given QueryBuilder with an orderBy() call.
     *
     * @param string $sortBy
     * @param string $direction
     *
     * @return QueryBuilder
     */
    private function sortQueryBuilder(QueryBuilder $qb, $sortBy = 'createdAt', $direction = 'desc')
    {
        return $qb->orderBy(\sprintf('e.%s', $sortBy), $direction);
    }
}
