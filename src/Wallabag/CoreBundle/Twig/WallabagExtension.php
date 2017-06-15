<?php

namespace Wallabag\CoreBundle\Twig;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Wallabag\CoreBundle\Notifications\NotificationInterface;
use Wallabag\CoreBundle\Repository\EntryRepository;
use Wallabag\CoreBundle\Repository\NotificationRepository;
use Wallabag\CoreBundle\Repository\TagRepository;
use Symfony\Component\Translation\TranslatorInterface;

class WallabagExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    private $tokenStorage;
    private $entryRepository;
    private $tagRepository;
    private $notificationRepository;
    private $lifeTime;
    private $nbNotifications;
    private $translator;

    public function __construct(EntryRepository $entryRepository, TagRepository $tagRepository, NotificationRepository $notificationRepository, TokenStorageInterface $tokenStorage, $lifeTime, $nbNotifications, TranslatorInterface $translator)
    {
        $this->entryRepository = $entryRepository;
        $this->tagRepository = $tagRepository;
        $this->notificationRepository = $notificationRepository;
        $this->tokenStorage = $tokenStorage;
        $this->lifeTime = $lifeTime;
        $this->nbNotifications = $nbNotifications;
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('removeWww', [$this, 'removeWww']),
            new \Twig_SimpleFilter('unread_notif', [$this, 'unreadNotif']),
        ];
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('count_entries', [$this, 'countEntries']),
            new \Twig_SimpleFunction('count_tags', [$this, 'countTags']),
            new \Twig_SimpleFunction('display_stats', [$this, 'displayStats']),
            new \Twig_SimpleFunction('get_notifications', [$this, 'getNotifications'])
        );
    }

    public function removeWww($url)
    {
        return preg_replace('/^www\./i', '', $url);
    }

    /**
     * @param $notifs
     * @return array
     */
    public function unreadNotif($notifs)
    {
        return array_filter($notifs, function (NotificationInterface $notif) {
            return !$notif->isRead();
        });
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

        return $this->tagRepository->countAllTags($user->getId());
    }

    public function getNotifications()
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if (null === $user || !is_object($user)) {
            return 0;
        }

        return $this->notificationRepository->findBy(
            ['user' => $user->getId()],
            ['timestamp' => 'DESC'],
            $this->nbNotifications
        );
    }

    /**
     * Display a single line about reading stats.
     *
     * @return string
     */
    public function displayStats()
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if (null === $user || !is_object($user)) {
            return 0;
        }

        $query = $this->entryRepository->getBuilderForArchiveByUser($user->getId())
            ->select('e.id')
            ->groupBy('e.id')
            ->getQuery();

        $query->useQueryCache(true);
        $query->useResultCache(true);
        $query->setResultCacheLifetime($this->lifeTime);

        $nbArchives = count($query->getArrayResult());

        $interval = $user->getCreatedAt()->diff(new \DateTime('now'));
        $nbDays = (int) $interval->format('%a') ?: 1;

        // force setlocale for date translation
        setlocale(LC_TIME, strtolower($user->getConfig()->getLanguage()).'_'.strtoupper(strtolower($user->getConfig()->getLanguage())));

        return $this->translator->trans('footer.stats', [
            '%user_creation%' => strftime('%e %B %Y', $user->getCreatedAt()->getTimestamp()),
            '%nb_archives%' => $nbArchives,
            '%per_day%' => round($nbArchives / $nbDays, 2),
        ]);
    }
}
