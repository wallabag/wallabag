<?php

namespace Wallabag\CoreBundle\Twig;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Wallabag\CoreBundle\Repository\EntryRepository;
use Wallabag\CoreBundle\Repository\TagRepository;

class WallabagExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    private $tokenStorage;
    private $entryRepository;
    private $tagRepository;
    private $lifeTime;

    public function __construct(EntryRepository $entryRepository = null, TagRepository $tagRepository = null, TokenStorageInterface $tokenStorage = null, $lifeTime = 0)
    {
        $this->entryRepository = $entryRepository;
        $this->tagRepository = $tagRepository;
        $this->tokenStorage = $tokenStorage;
        $this->lifeTime = $lifeTime;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('removeWww', [$this, 'removeWww']),
        ];
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('count_entries', [$this, 'countEntries']),
            new \Twig_SimpleFunction('count_tags', [$this, 'countTags']),
        );
    }

    public function removeWww($url)
    {
        return preg_replace('/^www\./i', '', $url);
    }

    /**
     * Return number of entries depending of the type (unread, archive, starred or all).
     *
     * @param string $type Type of entries to count
     *
     * @return int
     */
    public function countEntries($type)
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if (null === $user || !is_object($user)) {
            return 0;
        }

        switch ($type) {
            case 'starred':
                $qb = $this->entryRepository->getBuilderForStarredByUser($user->getId());
                break;

            case 'archive':
                $qb = $this->entryRepository->getBuilderForArchiveByUser($user->getId());
                break;

            case 'unread':
                $qb = $this->entryRepository->getBuilderForUnreadByUser($user->getId());
                break;

            case 'all':
                $qb = $this->entryRepository->getBuilderForAllByUser($user->getId());
                break;

            default:
                throw new \InvalidArgumentException(sprintf('Type "%s" is not implemented.', $type));
        }

        // THANKS to PostgreSQL we CAN'T make a DEAD SIMPLE count(e.id)
        // ERROR: column "e0_.id" must appear in the GROUP BY clause or be used in an aggregate function
        $query = $qb
            ->select('e.id')
            ->groupBy('e.id')
            ->getQuery();

        $query->useQueryCache(true);
        $query->useResultCache(true);
        $query->setResultCacheLifetime($this->lifeTime);

        return count($query->getArrayResult());
    }

    /**
     * Return number of tags.
     *
     * @return int
     */
    public function countTags()
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if (null === $user || !is_object($user)) {
            return 0;
        }

        $data = $this->tagRepository->findAllTags($user->getId());

        return count($data);
    }

    public function getName()
    {
        return 'wallabag_extension';
    }
}
