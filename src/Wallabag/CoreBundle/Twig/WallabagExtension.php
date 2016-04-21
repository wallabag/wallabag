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

        $unreadEntries = $this->repository->getBuilderForUnreadByUser($user->getId())->getQuery()->getResult();
        $starredEntries = $this->repository->getBuilderForStarredByUser($user->getId())->getQuery()->getResult();
        $archivedEntries = $this->repository->getBuilderForArchiveByUser($user->getId())->getQuery()->getResult();
        $allEntries = $this->repository->getBuilderForAllByUser($user->getId())->getQuery()->getResult();

        return array(
            'unreadEntries' => count($unreadEntries),
            'starredEntries' => count($starredEntries),
            'archivedEntries' => count($archivedEntries),
            'allEntries' => count($allEntries),
        );
    }

    public function getName()
    {
        return 'wallabag_extension';
    }
}
