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

    public function removeWww($url)
    {
        return preg_replace('/^www\./i', '', $url);
    }

    public function getGlobals()
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if (null === $user || !is_object($user)) {
            return array();
        }

        $unreadEntries = $this->repository->enableCache(
            $this->repository->getBuilderForUnreadByUser($user->getId())->getQuery()
        );

        $starredEntries = $this->repository->enableCache(
            $this->repository->getBuilderForStarredByUser($user->getId())->getQuery()
        );

        $archivedEntries = $this->repository->enableCache(
            $this->repository->getBuilderForArchiveByUser($user->getId())->getQuery()
        );

        $allEntries = $this->repository->enableCache(
            $this->repository->getBuilderForAllByUser($user->getId())->getQuery()
        );

        return array(
            'unreadEntries' => count($unreadEntries->getResult()),
            'starredEntries' => count($starredEntries->getResult()),
            'archivedEntries' => count($archivedEntries->getResult()),
            'allEntries' => count($allEntries->getResult()),
        );
    }

    public function getName()
    {
        return 'wallabag_extension';
    }
}
