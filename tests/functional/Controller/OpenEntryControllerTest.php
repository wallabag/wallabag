<?php

namespace Wallabag\Tests\Functional\Controller;

use Wallabag\Entity\Entry;
use Wallabag\Tests\Functional\WallabagTestCase;

class OpenEntryControllerTest extends WallabagTestCase
{
    public function openingModes()
    {
        return [
            [false, 'https://example.com/article', false],
            [true, 'https://example.com/article?one=1&two=2', true],
            [true, 'http://example.com/article', true],
            [true, 'javascript:alert(1)', false],
            [true, '//example.com/article', false],
            [true, '', false],
        ];
    }

    /**
     * @dataProvider openingModes
     */
    public function testOpen(bool $original, string $url, bool $external): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $client->request('GET', '/config');
        $user = $this->getLoggedInUser();
        $user->getConfig()->setOpenOriginalUrl($original);
        $entry = new Entry($user);
        $entry->setUrl($url);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();
        $id = $entry->getId();

        $client->request('GET', '/open/' . $id);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertSame($external ? $url : '/view/' . $id, $client->getResponse()->headers->get('location'));
        $this->getEntityManager()->clear();
        $this->assertFalse($this->getEntityManager()->find(Entry::class, $id)->isArchived());

        if ($original) {
            $client->request('GET', '/view/' . $id);
            $this->assertSame(200, $client->getResponse()->getStatusCode());
        }
    }

    public function testCannotOpenAnotherUsersEntry(): void
    {
        $this->logInAs('admin');
        $this->getTestClient()->request('GET', '/config');
        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('https://example.com/private');
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();
        $id = $entry->getId();

        $this->logInAs('bob');
        $this->getTestClient()->request('GET', '/open/' . $id);
        $this->assertSame(404, $this->getTestClient()->getResponse()->getStatusCode());
    }

    public function testCardsAndListsKeepReaderAccessible(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $client->request('GET', '/config');
        $user = $this->getLoggedInUser();
        $config = $user->getConfig();
        $config->setOpenOriginalUrl(true)->setDisplayThumbnails(true);
        $entries = [];
        foreach (['text/html', 'image/png'] as $mimetype) {
            $entry = new Entry($user);
            $entry->setUrl('https://example.com/' . $mimetype);
            $entry->setMimetype($mimetype);
            $entry->setPreviewPicture('https://example.com/preview.png');
            $this->getEntityManager()->persist($entry);
            $entries[] = $entry;
        }
        $this->getEntityManager()->flush();
        $ids = array_map(static function (Entry $entry) { return $entry->getId(); }, $entries);

        foreach ([0, 1] as $mode) {
            $config = $this->getEntityManager()->find(\Wallabag\Entity\Config::class, $config->getId());
            $config->setListMode($mode);
            $this->getEntityManager()->flush();
            $crawler = $client->request('GET', '/all/list');
            $this->assertSame(200, $client->getResponse()->getStatusCode());
            foreach ($ids as $id) {
                $card = $crawler->filter('#entry-' . $id);
                $this->assertGreaterThanOrEqual(2, $card->filter('a[href="/open/' . $id . '"]')->count());
                $this->assertCount(1, $card->filter('a[href="/view/' . $id . '"]'));
                $this->assertCount(1, $card->filter('form[action^="/archive/' . $id . '"]'));
            }
        }
    }
}
