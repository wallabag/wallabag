<?php

namespace Wallabag\Twig;

use GuzzleHttp\Psr7\Query;
use GuzzleHttp\Psr7\Uri;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Repository\EntryRepository;

class WallabagExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly EntryRepository $entryRepository,
        private readonly TokenStorageInterface $tokenStorage,
        private $lifeTime,
        private readonly TranslatorInterface $translator,
        private readonly string $projectDir,
        private readonly string $articleReportingUrl,
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
            new TwigFilter('removeDataAttributes', $this->removeDataAttributes(...)),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('display_stats', $this->displayStats(...)),
            new TwigFunction('asset_file_exists', $this->assetFileExists(...)),
            new TwigFunction('theme_class', $this->themeClass(...)),
            new TwigFunction('generate_article_reporting_url', $this->generateArticleReportingUrl(...)),
        ];
    }

    public function generateArticleReportingUrl(Entry $entry): string
    {
        $uri = new Uri($this->articleReportingUrl);
        $query = Query::parse($uri->getQuery(), \PHP_QUERY_RFC3986);
        $query['body'] = $entry->getUrl();

        return (string) $uri->withQuery(Query::build($query, \PHP_QUERY_RFC3986));
    }

    public function removeDataAttributes(?string $html): string
    {
        if (null === $html || false === stripos($html, 'data-')) {
            return (string) $html;
        }

        $crawler = new Crawler('<!DOCTYPE html><html><body>' . $html . '</body></html>');

        foreach ($crawler->filterXPath('//*') as $element) {
            \assert($element instanceof \DOMElement);

            foreach (iterator_to_array($element->attributes) as $attribute) {
                if (str_starts_with($attribute->name, 'data-')) {
                    $element->removeAttributeNode($attribute);
                }
            }
        }

        return $crawler->filterXPath('//body')->html();
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
