<?php

namespace Wallabag\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Helper\ContentProxy;

class EntryFixtures extends Fixture implements DependentFixtureInterface
{
    private const URLS = [
        'https://blog.narf.ssji.net/2025/01/17/manually-sending-document-contents-to-wallabag/',
        'https://nicolas.loeuillet.org/billets/2025/01/22/wallabagit-a-8-ans-merci-omnivore/',
        'https://nicolas.loeuillet.org/billets/2025/01/28/que-sest-il-passe-en-2024/',
        'https://qmacro.org/blog/posts/2025/03/10/migrating-github-issue-based-url-bookmarks-to-wallabag/',
        'https://datanalytics.com/2025/05/08/wallabag/',
        'https://isedu.top/index.php/archives/300/',
        'https://www.justgeek.fr/alternatives-pocket-sauvegarde-articles-138495/',
        'https://lecrabeinfo.net/guides/pocket-4-alternatives-a-pocket-pour-sauvegarder-vos-articles-sur-le-web/',
        'https://www.ti-nuage.fr/blog/2025/05/de-pocket-a-wallabag/',
        'https://www.claudiuscoenen.de/2025/05/pocket-to-wallabag-hardcore-edition/',
        'https://planet.kde.org/matija-suklje-hook-2025-05-24-trying-out-koreader-and-wallabag-the-first-few-days-and-months/',
        'https://www.angelaambroz.com/posts/selfhosted_read_it_later/',
        'https://jqno.nl/post/2025/06/04/reading-blogs-on-my-kobo-ereader-via-wallabag/',
        'https://wallabag.org/news/20250627-apero-wallabag/',
        'https://manualdousuario.net/wallabag-alternativa-omnivore-pocket/',
        'https://nicolas.loeuillet.org/billets/2025/07/08/rip-pocket/',
        'https://www.blog.brightcoding.dev/2025/07/15/save-web-articles-to-read-later-the-self-host-way/',
        'https://linuxfr.org/news/pocket-est-mort-vive-wallabag',
        'https://justman.fr/on-the-web/fin-de-pocket-occasion-de-decouvrir-wallabag-alternative-libre',
        'https://goodtech.info/alternative-open-source-pocket-wallabag/',
        'https://blog.jonsdocs.org.uk/2025/07/27/switching-to-wallabag-pocket-replacement/',
        'https://hamatti.org/posts/wallabag-i-choose-you/',
        'https://blog.jonsdocs.org.uk/2025/08/18/more-experience-with-wallabag/',
        'https://www.xda-developers.com/wallabag-is-the-best-self-hosted-pocket-alternative/',
        'https://zenn.dev/ryobeam/articles/20251021-self-host-wallabag',
    ];

    // Entries archived by index (0-based)
    private const ARCHIVED_INDICES = [5, 6, 7, 8, 9, 10, 11, 12];

    // Entries starred by index (0-based)
    private const STARRED_INDICES = [13, 16, 20];

    public function __construct(
        private readonly ContentProxy $contentProxy,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference('dev-user', User::class);

        foreach (self::URLS as $index => $url) {
            $entry = new Entry($user);

            $this->contentProxy->updateEntry($entry, $url);

            if (\in_array($index, self::ARCHIVED_INDICES, true)) {
                $entry->setArchived(true);
                $entry->setArchivedAt(new \DateTime());
            }

            if (\in_array($index, self::STARRED_INDICES, true)) {
                $entry->setStarred(true);
                $entry->setStarredAt(new \DateTime());
            }

            $manager->persist($entry);
            $manager->flush();

            $this->addReference('entry-' . $index, $entry);
        }
    }

    public function getDependencies(): array
    {
        return [
            TaggingRuleFixtures::class,
            TagFixtures::class,
        ];
    }
}
