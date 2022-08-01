<?php

namespace Wallabag\CoreBundle\Twig;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Wallabag\CoreBundle\Repository\EntryRepository;
use Wallabag\CoreBundle\Repository\TagRepository;
use Wallabag\UserBundle\Entity\User;

class WallabagExtension extends AbstractExtension implements GlobalsInterface
{
    private $tokenStorage;
    private $entryRepository;
    private $tagRepository;
    private $lifeTime;
    private $translator;
    private $rootDir;

    public function __construct(EntryRepository $entryRepository, TagRepository $tagRepository, TokenStorageInterface $tokenStorage, $lifeTime, TranslatorInterface $translator, string $rootDir)
    {
        $this->entryRepository = $entryRepository;
        $this->tagRepository = $tagRepository;
        $this->tokenStorage = $tokenStorage;
        $this->lifeTime = $lifeTime;
        $this->translator = $translator;
        $this->rootDir = $rootDir;
    }

    public function getGlobals()
    {
        return [];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('removeWww', [$this, 'removeWww']),
            new TwigFilter('removeScheme', [$this, 'removeScheme']),
            new TwigFilter('removeSchemeAndWww', [$this, 'removeSchemeAndWww']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('count_entries', [$this, 'countEntries']),
            new TwigFunction('count_tags', [$this, 'countTags']),
            new TwigFunction('display_stats', [$this, 'displayStats']),
            new TwigFunction('asset_file_exists', [$this, 'assetFileExists']),
            new TwigFunction('theme_class', [$this, 'themeClass']),
        ];
    }

    public function removeWww($url)
    {
        return preg_replace('/^www\./i', '', $url);
    }

    public function removeScheme($url)
    {
        return preg_replace('#^https?://#i', '', $url);
    }

    public function removeSchemeAndWww($url)
    {
        return $this->removeWww($this->removeScheme($url));
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

        if (!$user instanceof User) {
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
            case 'annotated':
                $qb = $this->entryRepository->getBuilderForAnnotationsByUser($user->getId());
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
        $query->enableResultCache($this->lifeTime);

        return \count($query->getArrayResult());
    }

    /**
     * Return number of tags.
     *
     * @return int
     */
    public function countTags()
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if (!$user instanceof User) {
            return 0;
        }

        return $this->tagRepository->countAllTags($user->getId());
    }

    /**
     * Display a single line about reading stats.
     *
     * @return string
     */
    public function displayStats()
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if (!$user instanceof User) {
            return 0;
        }

        $query = $this->entryRepository->getBuilderForArchiveByUser($user->getId())
            ->select('e.id')
            ->groupBy('e.id')
            ->getQuery();

        $query->useQueryCache(true);
        $query->enableResultCache($this->lifeTime);

        $nbArchives = \count($query->getArrayResult());

        $interval = $user->getCreatedAt()->diff(new \DateTime('now'));
        $nbDays = (int) $interval->format('%a') ?: 1;

        // force setlocale for date translation
        $locale = strtolower($user->getConfig()->getLanguage()) . '_' . strtoupper(strtolower($user->getConfig()->getLanguage()));
        $intlDateFormatter = new \IntlDateFormatter($locale, \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);

        return $this->translator->trans('footer.stats', [
            '%user_creation%' => $intlDateFormatter->format($user->getCreatedAt()),
            '%nb_archives%' => $nbArchives,
            '%per_day%' => round($nbArchives / $nbDays, 2),
        ]);
    }

    public function assetFileExists($name)
    {
        return file_exists(realpath($this->rootDir . '/../web/' . $name));
    }

    public function themeClass()
    {
        return isset($_COOKIE['theme']) && 'dark' === $_COOKIE['theme'] ? 'dark-theme' : '';
    }

    public function getName()
    {
        return 'wallabag_extension';
    }
}
