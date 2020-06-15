<?php

namespace Wallabag\CoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\UserBundle\DataFixtures\UserFixtures;

class EntryFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager): void
    {
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
                'tags' => ['bar-tag'],
            ],
        ];

        foreach ($entries as $reference => $item) {
            $entry = new Entry($this->getReference($item['user']));
            $entry->setUrl($item['url']);
            $entry->setReadingTime($item['reading_time']);
            $entry->setDomainName($item['domain']);
            $entry->setMimetype($item['mime']);
            $entry->setTitle($item['title']);
            $entry->setContent($item['content']);
            $entry->setLanguage($item['language']);

            if (isset($item['tags'])) {
                foreach ($item['tags'] as $tag) {
                    $entry->addTag($this->getReference($tag));
                }
            }

            if (isset($item['origin'])) {
                $entry->setOriginUrl($item['origin']);
            }

            if (isset($item['starred'])) {
                $entry->setStarred($item['starred']);
            }

            if (isset($item['archived'])) {
                $entry->setArchived($item['archived']);
            }

            if (isset($item['preview'])) {
                $entry->setPreviewPicture($item['preview']);
            }

            $manager->persist($entry);
            $this->addReference($reference, $entry);
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            UserFixtures::class,
            TagFixtures::class,
        ];
    }
}
