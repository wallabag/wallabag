<?php

namespace Wallabag\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;

class EntryRepository extends EntityRepository
{
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
     * Retrieves read entries for a user.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    public function getBuilderForArchiveByUser($userId)
    {
        return $this
            ->getSortedQueryBuilderByUser($userId)
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
     * Retrieves entries filtered with a search term for a user.
     *
     * @param int    $userId
     * @param string $term
     * @param string $currentRoute
     *
     * @return QueryBuilder
     */
    public function getBuilderForSearchByUser($userId, $term, $currentRoute)
    {
        $qb = $this
            ->getSortedQueryBuilderByUser($userId);

        if ('starred' === $currentRoute) {
            $qb->andWhere('e.isStarred = true');
        } elseif ('unread' === $currentRoute) {
            $qb->andWhere('e.isArchived = false');
        } elseif ('archive' === $currentRoute) {
            $qb->andWhere('e.isArchived = true');
        }

        // We lower() all parts here because PostgreSQL 'LIKE' verb is case-sensitive
        $qb
            ->andWhere('lower(e.content) LIKE lower(:term) OR lower(e.title) LIKE lower(:term) OR lower(e.url) LIKE lower(:term)')->setParameter('term', '%' . $term . '%')
            ->leftJoin('e.tags', 't')
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
        return $this
            ->sortQueryBuilder($this->getRawBuilderForUntaggedByUser($userId));
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
     *
     * @return Pagerfanta
     */
    public function findEntries($userId, $isArchived = null, $isStarred = null, $isPublic = null, $sort = 'created', $order = 'ASC', $since = 0, $tags = '')
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.tags', 't')
            ->where('e.user = :userId')->setParameter('userId', $userId);

        if (null !== $isArchived) {
            $qb->andWhere('e.isArchived = :isArchived')->setParameter('isArchived', (bool) $isArchived);
        }

        if (null !== $isStarred) {
            $qb->andWhere('e.isStarred = :isStarred')->setParameter('isStarred', (bool) $isStarred);
        }

        if (null !== $isPublic) {
            $qb->andWhere('e.uid IS ' . (true === $isPublic ? 'NOT' : '') . ' NULL');
        }

        if ($since > 0) {
            $qb->andWhere('e.updatedAt > :since')->setParameter('since', new \DateTime(date('Y-m-d H:i:s', $since)));
        }

        if (\is_string($tags) && '' !== $tags) {
            foreach (explode(',', $tags) as $i => $tag) {
                $entryAlias = 'e' . $i;
                $tagAlias = 't' . $i;

                // Complexe queries to ensure multiple tags are associated to an entry
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

        if ('created' === $sort) {
            $qb->orderBy('e.id', $order);
        } elseif ('updated' === $sort) {
            $qb->orderBy('e.updatedAt', $order);
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
     * @param Tag $tag
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
     * @param Array<Tag> $tags
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
    public function findAllByTagId($userId, $tagId)
    {
        return $this->getSortedQueryBuilderByUser($userId)
            ->innerJoin('e.tags', 't')
            ->andWhere('t.id = :tagId')->setParameter('tagId', $tagId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find an entry by its url and its owner.
     * If it exists, return the entry otherwise return false.
     *
     * @param $url
     * @param $userId
     *
     * @return Entry|bool
     */
    public function findByUrlAndUserId($url, $userId)
    {
        $res = $this->createQueryBuilder('e')
            ->where('e.url = :url')->setParameter('url', urldecode($url))
            ->andWhere('e.user = :user_id')->setParameter('user_id', $userId)
            ->getQuery()
            ->getResult();

        if (\count($res)) {
            return current($res);
        }

        return false;
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
     * Used when a user want to reset all informations.
     *
     * @param int $userId
     */
    public function removeAllByUserId($userId)
    {
        $this->getEntityManager()
            ->createQuery('DELETE FROM Wallabag\CoreBundle\Entity\Entry e WHERE e.user = :userId')
            ->setParameter('userId', $userId)
            ->execute();
    }

    public function removeArchivedByUserId($userId)
    {
        $this->getEntityManager()
            ->createQuery('DELETE FROM Wallabag\CoreBundle\Entity\Entry e WHERE e.user = :userId AND e.isArchived = TRUE')
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
     * Find all entries by url and owner.
     *
     * @param $url
     * @param $userId
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
     * @param QueryBuilder $qb
     * @param string       $sortBy
     * @param string       $direction
     *
     * @return QueryBuilder
     */
    private function sortQueryBuilder(QueryBuilder $qb, $sortBy = 'createdAt', $direction = 'desc')
    {
        return $qb
            ->orderBy(sprintf('e.%s', $sortBy), $direction);
    }
}
