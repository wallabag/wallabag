<?php

namespace Wallabag\Twig;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Wallabag\Entity\User;
use Wallabag\Repository\AnnotationRepository;
use Wallabag\Repository\EntryRepository;
use Wallabag\Repository\TagRepository;

class WallabagExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly EntryRepository $entryRepository,
        private readonly AnnotationRepository $annotationRepository,
        private readonly TagRepository $tagRepository,
        private readonly TokenStorageInterface $tokenStorage,
        private $lifeTime,
        private readonly TranslatorInterface $translator,
        private readonly string $projectDir,
    ) {
    }

    public function getGlobals(): array
    {
        return [];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('removeWww', $this->removeWww(...)),
            new TwigFilter('removeScheme', $this->removeScheme(...)),
            new TwigFilter('removeSchemeAndWww', $this->removeSchemeAndWww(...)),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('count_entries', $this->countEntries(...)),
            new TwigFunction('count_tags', $this->countTags(...)),
            new TwigFunction('display_stats', $this->displayStats(...)),
            new TwigFunction('asset_file_exists', $this->assetFileExists(...)),
            new TwigFunction('theme_class', $this->themeClass(...)),
        ];
    }

    public function removeWww($url)
    {
        if (!\is_string($url)) {
            return $url;
        }

        return preg_replace('/^www\./i', '', $url);
    }

    public function removeScheme($url)
    {
        if (!\is_string($url)) {
            return $url;
        }

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

        $qb = match ($type) {
            'starred' => $this->entryRepository->getCountBuilderForStarredByUser($user->getId())->select('COUNT(e.id)'),
            'archive' => $this->entryRepository->getCountBuilderForArchiveByUser($user->getId())->select('COUNT(e.id)'),
            'unread' => $this->entryRepository->getCountBuilderForUnreadByUser($user->getId())->select('COUNT(e.id)'),
            'annotated' => $this->annotationRepository->getCountBuilderByUser($user->getId())->select('COUNT(DISTINCT e.entry)'),
            'all' => $this->entryRepository->getCountBuilderForAllByUser($user->getId())->select('COUNT(e.id)'),
            default => throw new \InvalidArgumentException(\sprintf('Type "%s" is not implemented.', $type)),
        };

        $query = $qb->getQuery();
        $query->useQueryCache(true);
        $query->enableResultCache($this->lifeTime);

        return $query->getSingleScalarResult();
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
            return '';
        }

        $query = $this->entryRepository->getCountBuilderForArchiveByUser($user->getId())
            ->select('COUNT(e.id)')
            ->getQuery();

        $query->useQueryCache(true);
        $query->enableResultCache($this->lifeTime);

        $nbArchives = $query->getSingleScalarResult();

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
        return file_exists(realpath($this->projectDir . '/web/' . $name));
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
