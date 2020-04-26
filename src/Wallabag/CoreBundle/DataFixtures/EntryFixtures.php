<?php

namespace Wallabag\CoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\UserBundle\DataFixtures\UserFixtures;

class EntryFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
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
                'created_at' => '2020-04-26 10:00:00',
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
                'created_at' => '2020-04-26 10:01:00',
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
                'created_at' => '2020-04-26 10:02:00',
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
                'created_at' => '2020-04-26 10:03:00',
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
                'created_at' => '2020-04-26 10:04:00',
                'language' => 'fr',
                'starred' => true,
                'starred_at' => '2042-04-26 10:04:00',
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
                'created_at' => '2020-04-26 10:05:00',
                'language' => 'de',
                'archived' => true,
                'archived_at' => '2020-04-26 10:05:00',
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
            $entry->setCreatedAt(new \DateTime($item['created_at']));
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
                $entry->setStarredAt(new \DateTime($item['starred_at']));
            }

            if (isset($item['archived'])) {
                $entry->setArchived($item['archived']);
                $entry->setArchivedAt(new \DateTime($item['archived_at']));
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
