<?php

namespace Wallabag\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Wallabag\Entity\Entry;
use Wallabag\Entity\Tag;
use Wallabag\Entity\User;

class EntryFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $now = new \DateTime();

        $entries = [
            'entry1' => [
                'user' => 'admin-user',
                'url' => 'http://0.0.0.0/entry1',
                'reading_time' => 11,
                'domain' => 'domain.io',
                'mime' => 'text/html',
                'title' => 'test title entry1',
                'content' => 'This is my content /o/',
                'language' => 'en',
                'tags' => ['foo-tag', 'baz-tag'],
            ],
            'entry2' => [
                'user' => 'admin-user',
                'url' => 'http://0.0.0.0/entry2',
                'reading_time' => 1,
                'domain' => 'domain.io',
                'mime' => 'text/html',
                'title' => 'test title entry2',
                'content' => 'This is my content /o/',
                'origin' => 'ftp://oneftp.tld',
                'language' => 'fr',
            ],
            'entry3' => [
                'user' => 'bob-user',
                'url' => 'http://0.0.0.0/entry3',
                'reading_time' => 1,
                'domain' => 'domain.io',
                'mime' => 'text/html',
                'title' => 'test title entry3',
                'content' => 'This is my content /o/',
                'language' => 'en',
                'tags' => ['foo-tag', 'bar-tag', 'bob-tag'],
            ],
            'entry4' => [
                'user' => 'admin-user',
                'url' => 'http://0.0.0.0/entry4',
                'reading_time' => 12,
                'domain' => 'domain.io',
                'mime' => 'text/html',
                'title' => 'test title entry4',
                'content' => 'This is my content /o/',
                'language' => 'en',
                'tags' => ['foo-tag', 'bar-tag'],
            ],
            'entry5' => [
                'user' => 'admin-user',
                'url' => 'http://0.0.0.0/entry5',
                'reading_time' => 12,
                'domain' => 'domain.io',
                'mime' => 'text/html',
                'title' => 'test title entry5',
                'content' => 'This is my content /o/',
                'language' => 'fr',
                'starred' => true,
                'starred_at' => $now,
                'preview' => 'http://0.0.0.0/image.jpg',
            ],
            'entry6' => [
                'user' => 'admin-user',
                'url' => 'http://0.0.0.0/entry6',
                'reading_time' => 12,
                'domain' => 'domain.io',
                'mime' => 'text/html',
                'title' => 'test title entry6',
                'content' => 'This is my content /o/',
                'language' => 'de',
                'archived' => true,
                'archived_at' => $now,
                'tags' => ['bar-tag'],
                'is_not_parsed' => true,
            ],
            'entry7' => [
                'user' => 'admin-user',
                'url' => 'http://0.0.0.0/entry7',
                'reading_time' => 12,
                'domain' => 'redirect.io',
                'mime' => 'text/html',
                'title' => 'test title entry7',
                'http_status' => '302',
                'content' => 'This is redirect/o/',
                'language' => 'de',
            ],
        ];

        foreach ($entries as $reference => $item) {
            $entry = new Entry($this->getReference($item['user'], User::class));
            $entry->setUrl($item['url']);
            $entry->setReadingTime($item['reading_time']);
            $entry->setDomainName($item['domain']);
            $entry->setMimetype($item['mime']);
            $entry->setTitle($item['title']);
            $entry->setContent($item['content']);
            $entry->setLanguage($item['language']);

            if (isset($item['tags'])) {
                foreach ($item['tags'] as $tag) {
                    $entry->addTag($this->getReference($tag, Tag::class));
                }
            }

            if (isset($item['origin'])) {
                $entry->setOriginUrl($item['origin']);
            }

            if (isset($item['starred'])) {
                $entry->setStarred($item['starred']);
            }

            if (isset($item['starred_at'])) {
                $entry->setStarredAt($item['starred_at']);
            }

            if (isset($item['archived'])) {
                $entry->setArchived($item['archived']);
            }

            if (isset($item['archived_at'])) {
                $entry->setArchivedAt($item['archived_at']);
            }

            if (isset($item['preview'])) {
                $entry->setPreviewPicture($item['preview']);
            }

            if (isset($item['is_not_parsed'])) {
                $entry->setNotParsed($item['is_not_parsed']);
            }

            if (isset($item['http_status'])) {
                $entry->setHttpStatus($item['http_status']);
            }

            $manager->persist($entry);
            $this->addReference($reference, $entry);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            TagFixtures::class,
        ];
    }
}
