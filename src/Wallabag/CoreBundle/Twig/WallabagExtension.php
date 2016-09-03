<?php

namespace Wallabag\CoreBundle\Twig;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Wallabag\CoreBundle\Repository\EntryRepository;

class WallabagExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    private $tokenStorage;
    private $repository;

    public function __construct(EntryRepository $repository = null, TokenStorageInterface $tokenStorage = null)
    {
        $this->repository = $repository;
        $this->tokenStorage = $tokenStorage;
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
        );
    }

    public function removeWww($url)
    {
        return preg_replace('/^www\./i', '', $url);
    }

    /**
     * Return number of entries depending of the type (unread, archive, starred or all)
     *
     * @param  string $type Type of entries to count
     *
     * @return int
     */
    public function countEntries($type)
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if (null === $user || !is_object($user)) {
            return [];
        }

        switch ($type) {
            case 'starred':
                $qb = $this->repository->getBuilderForStarredByUser($user->getId());
                break;

            case 'archive':
                $qb = $this->repository->getBuilderForArchiveByUser($user->getId());
                break;

            case 'unread':
                $qb = $this->repository->getBuilderForUnreadByUser($user->getId());
                break;

            case 'all':
                $qb = $this->repository->getBuilderForAllByUser($user->getId());
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

        $data =$this->repository
            ->enableCache($query)
            ->getArrayResult();

        return count($data);
    }

    public function getName()
    {
        return 'wallabag_extension';
    }
}
