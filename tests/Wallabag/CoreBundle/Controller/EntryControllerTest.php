<?php

namespace Tests\Wallabag\CoreBundle\Controller;

use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\SiteCredential;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Helper\ContentProxy;

class EntryControllerTest extends WallabagCoreTestCase
{
    const AN_URL_CONTAINING_AN_ARTICLE_WITH_IMAGE = 'https://www.lemonde.fr/judo/article/2017/11/11/judo-la-decima-de-teddy-riner_5213605_1556020.html';
    public $downloadImagesEnabled = false;
    public $url = 'https://www.lemonde.fr/pixels/article/2019/06/18/ce-qu-il-faut-savoir-sur-le-libra-la-cryptomonnaie-de-facebook_5477887_4408996.html';

    /**
     * @after
     *
     * Ensure download_images_enabled is disabled after each script
     */
    public function tearDownImagesEnabled()
    {
        if ($this->downloadImagesEnabled) {
            $client = static::createClient();
            $client->getContainer()->get('craue_config')->set('download_images_enabled', 0);

            $this->downloadImagesEnabled = false;
        }
    }

    public function testLogin()
    {
        $client = $this->getClient();

        $client->request('GET', '/new');

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('login', $client->getResponse()->headers->get('location'));
    }

    /**
     * @group NetworkCalls
     */
    public function testQuickstart()
    {
        $this->logInAs('empty');
        $client = $this->getClient();

        $client->request('GET', '/unread/list');
        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('quickstart.intro.title', $body[0]);

        // Test if quickstart is disabled when user has 1 entry
        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $this->url,
        ];

        $client->submit($form, $data);
        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();

        $crawler = $client->request('GET', '/unread/list');
        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('entry.list.number_on_the_page', $body[0]);
    }

    public function testGetNew()
    {
        $this->logInAs('admin');
        $this->useTheme('baggy');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertCount(1, $crawler->filter('input[type=url]'));
        $this->assertCount(1, $crawler->filter('form[name=entry]'));
    }

    /**
     * @group NetworkCalls
     */
    public function testPostNewViaBookmarklet()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/');

        $this->assertCount(4, $crawler->filter('li.entry'));

        // Good URL
        $client->request('GET', '/bookmarklet', ['url' => $this->url]);
        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        $crawler = $client->request('GET', '/');
        $this->assertCount(5, $crawler->filter('li.entry'));

        $em = $client->getContainer()
            ->get('doctrine.orm.entity_manager');
        $entry = $em
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId($this->url, $this->getLoggedInUserId());
        $em->remove($entry);
        $em->flush();
    }

    public function testPostNewEmpty()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $crawler = $client->submit($form);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $alert = $crawler->filter('form ul li')->extract(['_text']));
        $this->assertSame('This value should not be blank.', $alert[0]);
    }

    /**
     * @group NetworkCalls
     */
    public function testPostNewOk()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->getContainer()->get('craue_config')->set('store_article_headers', 1);

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $this->url,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId($this->url, $this->getLoggedInUserId());

        $author = $content->getPublishedBy();

        $this->assertInstanceOf('Wallabag\CoreBundle\Entity\Entry', $content);
        $this->assertSame($this->url, $content->getUrl());
        $this->assertStringContainsString('la cryptomonnaie de Facebook', $content->getTitle());
        $this->assertSame('fr', $content->getLanguage());
        $this->assertArrayHasKey('x-frame-options', $content->getHeaders());
        $client->getContainer()->get('craue_config')->set('store_article_headers', 0);
    }

    /**
     * @group NetworkCalls
     */
    public function testPostWithMultipleAuthors()
    {
        $url = 'https://www.liberation.fr/planete/2017/04/05/donald-trump-et-xi-jinping-tentative-de-flirt-en-floride_1560768';
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $url,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId($url, $this->getLoggedInUserId());

        $this->assertInstanceOf('Wallabag\CoreBundle\Entity\Entry', $content);
        $authors = $content->getPublishedBy();
        $this->assertSame('2017-04-05 19:26:13', $content->getPublishedAt()->format('Y-m-d H:i:s'));
        $this->assertSame('fr', $content->getLanguage());
        $this->assertSame('Raphaël Balenieri', $authors[0]);
        $this->assertSame('Frédéric Autran', $authors[1]);
    }

    public function testPostNewOkUrlExist()
    {
        $this->logInAs('admin');

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $client = $this->getClient();

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $this->url,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/view/', $client->getResponse()->getTargetUrl());
    }

    /**
     * @group NetworkCalls
     */
    public function testPostNewOkUrlExistWithAccent()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $url = 'https://www.aritylabs.com/post/106091708292/des-contr%C3%B4leurs-optionnels-gr%C3%A2ce-%C3%A0-constmissing';

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $url,
        ];

        $client->submit($form, $data);

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $url,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/view/', $client->getResponse()->getTargetUrl());
    }

    /**
     * @group NetworkCalls
     */
    public function testPostNewOkUrlExistWithRedirection()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $url = 'https://wllbg.org/test-redirect/c51c';

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $url,
        ];

        $client->submit($form, $data);

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $url,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/view/', $client->getResponse()->getTargetUrl());
    }

    /**
     * @group NetworkCalls
     */
    public function testPostNewThatWillBeTagged()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $url = 'https://github.com/wallabag/wallabag',
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/', $client->getResponse()->getTargetUrl());

        $em = $client->getContainer()
            ->get('doctrine.orm.entity_manager');
        $entry = $em
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUrl($url);
        $tags = $entry->getTags();

        $this->assertCount(2, $tags);
        $this->assertContains('wallabag', $tags);
        $this->assertSame('en', $entry->getLanguage());

        $em->remove($entry);
        $em->flush();

        // and now re-submit it to test the cascade persistence for tags after entry removal
        // related https://github.com/wallabag/wallabag/issues/2121
        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $url = 'https://github.com/wallabag/wallabag/tree/master',
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/', $client->getResponse()->getTargetUrl());

        $entry = $em
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUrl($url);

        $tags = $entry->getTags();

        $this->assertCount(2, $tags);
        $this->assertContains('wallabag', $tags);

        $em->remove($entry);
        $em->flush();
    }

    public function testArchive()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/archive/list');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testUntagged()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/untagged/list');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testStarred()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/starred/list');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testRangeException()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/all/list/900');

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertSame('/all/list', $client->getResponse()->getTargetUrl());
    }

    public function testView()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('http://example.com/foo');
        $entry->setTitle('title foo');
        $entry->setContent('foo bar baz');
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $crawler = $client->request('GET', '/view/' . $entry->getId());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString($entry->getTitle(), $body[0]);
    }

    /**
     * @group NetworkCalls
     */
    public function testReload()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $entry->setTitle('title foo');
        $entry->setContent('');
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $client->request('GET', '/reload/' . $entry->getId());

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $entry = $this->getEntityManager()
            ->getRepository('WallabagCoreBundle:Entry')
            ->find($entry->getId());

        $this->assertNotEmpty($entry->getContent());
    }

    public function testReloadWithFetchingFailed()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('http://0.0.0.0/failed.html');
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $client->request('GET', '/reload/' . $entry->getId());

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        // force EntityManager to clear previous entity
        // otherwise, retrieve the same entity will retrieve change from the previous request :0
        $this->getEntityManager()->clear();
        $newContent = $this->getEntityManager()
            ->getRepository('WallabagCoreBundle:Entry')
            ->find($entry->getId());

        $this->assertNotSame($client->getContainer()->getParameter('wallabag_core.fetching_error_message'), $newContent->getContent());
    }

    public function testEdit()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $crawler = $client->request('GET', '/edit/' . $entry->getId());

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertCount(1, $crawler->filter('input[id=entry_title]'));
        $this->assertCount(1, $crawler->filter('button[id=entry_save]'));
    }

    public function testEditUpdate()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $crawler = $client->request('GET', '/edit/' . $entry->getId());

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=entry_save]')->form();

        $data = [
            'entry[title]' => 'My updated title hehe :)',
            'entry[origin_url]' => 'https://example.io',
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $title = $crawler->filter('div[id=article] h1')->extract(['_text']));
        $this->assertStringContainsString('My updated title hehe :)', $title[0]);
        $this->assertGreaterThan(1, $stats = $crawler->filter('div[class="tools grey-text"] ul[class=stats] li a[class="tool grey-text"]')->extract(['_text']));
        $this->assertStringContainsString('example.io', trim($stats[1]));
    }

    public function testEditRemoveOriginUrl()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $crawler = $client->request('GET', '/edit/' . $entry->getId());

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=entry_save]')->form();

        $data = [
            'entry[title]' => 'My updated title hehe :)',
            'entry[origin_url]' => '',
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $title = $crawler->filter('div[id=article] h1')->extract(['_text']);
        $this->assertGreaterThan(1, $title);
        $this->assertStringContainsString('My updated title hehe :)', $title[0]);

        $stats = $crawler->filter('div[class="tools grey-text"] ul[class=stats] li a[class="tool grey-text"]')->extract(['_text']);
        $this->assertCount(1, $stats);
        $this->assertStringNotContainsString('example.io', trim($stats[0]));
    }

    public function testToggleArchive()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $client->request('GET', '/archive/' . $entry->getId());

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $res = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->find($entry->getId());

        $this->assertSame(1, $res->isArchived());
    }

    public function testToggleStar()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $client->request('GET', '/star/' . $entry->getId());

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $res = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneById($entry->getId());

        $this->assertSame(1, $res->isStarred());
    }

    public function testDelete()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $client->request('GET', '/delete/' . $entry->getId());

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $client->request('GET', '/delete/' . $entry->getId());

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * It will create a new entry.
     * Browse to it.
     * Then remove it.
     *
     * And it'll check that user won't be redirected to the view page of the content when it had been removed
     */
    public function testViewAndDelete()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $em = $client->getContainer()
            ->get('doctrine.orm.entity_manager');

        // add a new content to be removed later
        $user = $em
            ->getRepository('WallabagUserBundle:User')
            ->findOneByUserName('admin');

        $content = new Entry($user);
        $content->setUrl('http://1.1.1.1/entry');
        $content->setReadingTime(12);
        $content->setDomainName('domain.io');
        $content->setMimetype('text/html');
        $content->setTitle('test title entry');
        $content->setContent('This is my content /o/');
        $content->updateArchived(true);
        $content->setLanguage('fr');

        $em->persist($content);
        $em->flush();

        $client->request('GET', '/view/' . $content->getId());
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/delete/' . $content->getId());
        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $client->followRedirect();
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testViewOtherUserEntry()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUsernameAndNotArchived('bob');

        $client->request('GET', '/view/' . $content->getId());

        $this->assertSame(403, $client->getResponse()->getStatusCode());
    }

    public function testFilterOnReadingTime()
    {
        $this->logInAs('admin');
        $client = $this->getClient();
        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $entry->setReadingTime(22);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $crawler = $client->request('GET', '/unread/list');

        $form = $crawler->filter('button[id=submit-filter]')->form();

        $data = [
            'entry_filter[readingTime][right_number]' => 22,
            'entry_filter[readingTime][left_number]' => 22,
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(1, $crawler->filter('li.entry'));
    }

    public function testFilterOnReadingTimeWithNegativeValue()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/unread/list');

        $form = $crawler->filter('button[id=submit-filter]')->form();

        $data = [
            'entry_filter[readingTime][right_number]' => -22,
            'entry_filter[readingTime][left_number]' => -22,
        ];

        $crawler = $client->submit($form, $data);

        // forcing negative value results in no entry displayed
        $this->assertCount(0, $crawler->filter('li.entry'));
    }

    public function testFilterOnReadingTimeOnlyUpper()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/all/list');
        $this->assertCount(5, $crawler->filter('li.entry'));

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $entry->setReadingTime(23);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $crawler = $client->request('GET', '/all/list');
        $this->assertCount(6, $crawler->filter('li.entry'));

        $form = $crawler->filter('button[id=submit-filter]')->form();

        $data = [
            'entry_filter[readingTime][right_number]' => 22,
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(5, $crawler->filter('li.entry'));
    }

    public function testFilterOnReadingTimeOnlyLower()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/unread/list');

        $form = $crawler->filter('button[id=submit-filter]')->form();

        $data = [
            'entry_filter[readingTime][left_number]' => 22,
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(0, $crawler->filter('li.entry'));

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $entry->setReadingTime(23);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $crawler = $client->submit($form, $data);
        $this->assertCount(1, $crawler->filter('li.entry'));
    }

    public function testFilterOnUnreadStatus()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/all/list');

        $form = $crawler->filter('button[id=submit-filter]')->form();

        $data = [
            'entry_filter[isUnread]' => true,
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(4, $crawler->filter('li.entry'));

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $entry->updateArchived(false);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $crawler = $client->submit($form, $data);

        $this->assertCount(5, $crawler->filter('li.entry'));
    }

    public function testFilterOnCreationDate()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $em = $this->getEntityManager();

        $today = new \DateTimeImmutable();
        $tomorrow = $today->add(new \DateInterval('P1D'));
        $yesterday = $today->sub(new \DateInterval('P1D'));

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('http://0.0.0.0/testFilterOnCreationDate');
        $entry->setCreatedAt($yesterday);
        $em->persist($entry);
        $em->flush();

        $crawler = $client->request('GET', '/unread/list');

        $form = $crawler->filter('button[id=submit-filter]')->form();

        $data = [
            'entry_filter[createdAt][left_date]' => $today->format('Y-m-d'),
            'entry_filter[createdAt][right_date]' => $tomorrow->format('Y-m-d'),
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(5, $crawler->filter('li.entry'));

        $data = [
            'entry_filter[createdAt][left_date]' => $today->format('Y-m-d'),
            'entry_filter[createdAt][right_date]' => $today->format('Y-m-d'),
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(5, $crawler->filter('li.entry'));

        $data = [
            'entry_filter[createdAt][left_date]' => '1970-01-01',
            'entry_filter[createdAt][right_date]' => '1970-01-01',
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(0, $crawler->filter('li.entry'));
    }

    public function testPaginationWithFilter()
    {
        $this->logInAs('admin');
        $client = $this->getClient();
        $crawler = $client->request('GET', '/config');

        $form = $crawler->filter('button[id=config_save]')->form();

        $data = [
            'config[items_per_page]' => '1',
        ];

        $client->submit($form, $data);

        $parameters = '?entry_filter%5BreadingTime%5D%5Bleft_number%5D=&entry_filter%5BreadingTime%5D%5Bright_number%5D=';

        $client->request('GET', 'unread/list' . $parameters);

        $this->assertStringContainsString($parameters, $client->getResponse()->getContent());

        // reset pagination
        $crawler = $client->request('GET', '/config');
        $form = $crawler->filter('button[id=config_save]')->form();
        $data = [
            'config[items_per_page]' => '12',
        ];
        $client->submit($form, $data);
    }

    public function testFilterOnDomainName()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/unread/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();
        $data = [
            'entry_filter[domainName]' => 'domain',
        ];

        $crawler = $client->submit($form, $data);
        $this->assertCount(5, $crawler->filter('li.entry'));

        $crawler = $client->request('GET', '/unread/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();
        $data = [
            'entry_filter[domainName]' => 'dOmain',
        ];

        $crawler = $client->submit($form, $data);
        $this->assertCount(5, $crawler->filter('li.entry'));

        $form = $crawler->filter('button[id=submit-filter]')->form();
        $data = [
            'entry_filter[domainName]' => 'wallabag',
        ];

        $crawler = $client->submit($form, $data);
        $this->assertCount(0, $crawler->filter('li.entry'));
    }

    public function testFilterOnStatus()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/unread/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();
        $form['entry_filter[isArchived]']->tick();
        $form['entry_filter[isStarred]']->untick();

        $crawler = $client->submit($form);
        $this->assertCount(1, $crawler->filter('li.entry'));

        $form = $crawler->filter('button[id=submit-filter]')->form();
        $form['entry_filter[isArchived]']->untick();
        $form['entry_filter[isStarred]']->tick();

        $crawler = $client->submit($form);
        $this->assertCount(1, $crawler->filter('li.entry'));
    }

    public function testFilterOnIsPublic()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/unread/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();
        $form['entry_filter[isPublic]']->tick();

        $crawler = $client->submit($form);
        $this->assertCount(0, $crawler->filter('li.entry'));
    }

    public function testPreviewPictureFilter()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/unread/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();
        $form['entry_filter[previewPicture]']->tick();

        $crawler = $client->submit($form);
        $this->assertCount(1, $crawler->filter('li.entry'));
    }

    public function testFilterOnLanguage()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $entry->setLanguage('fr');
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $crawler = $client->request('GET', '/unread/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();
        $data = [
            'entry_filter[language]' => 'fr',
        ];

        $crawler = $client->submit($form, $data);
        $this->assertCount(3, $crawler->filter('li.entry'));

        $form = $crawler->filter('button[id=submit-filter]')->form();
        $data = [
            'entry_filter[language]' => 'en',
        ];

        $crawler = $client->submit($form, $data);
        $this->assertCount(2, $crawler->filter('li.entry'));
    }

    public function testShareEntryPublicly()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        // sharing is enabled
        $client->getContainer()->get('craue_config')->set('share_public', 1);

        $content = new Entry($this->getLoggedInUser());
        $content->setUrl($this->url);
        $this->getEntityManager()->persist($content);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        // no uid
        $client->request('GET', '/share/' . $content->getUid());
        $this->assertSame(404, $client->getResponse()->getStatusCode());

        // generating the uid
        $client->request('GET', '/share/' . $content->getId());
        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $shareUrl = $client->getResponse()->getTargetUrl();

        // use a new client to have a fresh empty session (instead of a logged one from the previous client)
        $client->restart();

        $client->request('GET', $shareUrl);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('max-age=25200', $client->getResponse()->headers->get('cache-control'));
        $this->assertStringContainsString('public', $client->getResponse()->headers->get('cache-control'));
        $this->assertStringContainsString('s-maxage=25200', $client->getResponse()->headers->get('cache-control'));
        $this->assertStringNotContainsString('no-cache', $client->getResponse()->headers->get('cache-control'));
        $this->assertStringContainsString('og:title', $client->getResponse()->getContent());
        $this->assertStringContainsString('og:type', $client->getResponse()->getContent());
        $this->assertStringContainsString('og:url', $client->getResponse()->getContent());
        $this->assertStringContainsString('og:image', $client->getResponse()->getContent());

        // sharing is now disabled
        $client->getContainer()->get('craue_config')->set('share_public', 0);
        $client->request('GET', '/share/' . $content->getUid());
        $this->assertSame(404, $client->getResponse()->getStatusCode());

        // removing the share
        $client->request('GET', '/share/delete/' . $content->getId());
        $this->assertSame(302, $client->getResponse()->getStatusCode());

        // share is now disable
        $client->request('GET', '/share/' . $content->getUid());
        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @group NetworkCalls
     */
    public function testNewEntryWithDownloadImagesEnabled()
    {
        $this->downloadImagesEnabled = true;
        $this->logInAs('admin');
        $client = $this->getClient();

        $url = self::AN_URL_CONTAINING_AN_ARTICLE_WITH_IMAGE;
        $client->getContainer()->get('craue_config')->set('download_images_enabled', 1);

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $url,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $em = $client->getContainer()
            ->get('doctrine.orm.entity_manager');

        $entry = $em
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId($url, $this->getLoggedInUserId());

        $this->assertInstanceOf('Wallabag\CoreBundle\Entity\Entry', $entry);
        $this->assertSame($url, $entry->getUrl());
        $this->assertStringContainsString('Judo', $entry->getTitle());
        // instead of checking for the filename (which might change) check that the image is now local
        $this->assertStringContainsString(rtrim($client->getContainer()->getParameter('domain_name'), '/') . '/assets/images/', $entry->getContent());

        $client->getContainer()->get('craue_config')->set('download_images_enabled', 0);
    }

    /**
     * @depends testNewEntryWithDownloadImagesEnabled
     */
    public function testRemoveEntryWithDownloadImagesEnabled()
    {
        $this->downloadImagesEnabled = true;
        $this->logInAs('admin');
        $client = $this->getClient();

        $url = self::AN_URL_CONTAINING_AN_ARTICLE_WITH_IMAGE;
        $client->getContainer()->get('craue_config')->set('download_images_enabled', 1);

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $url,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId($url, $this->getLoggedInUserId());

        $client->request('GET', '/delete/' . $content->getId());

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $client->getContainer()->get('craue_config')->set('download_images_enabled', 0);
    }

    public function testRedirectToHomepage()
    {
        $this->logInAs('empty');
        $client = $this->getClient();

        // Redirect to homepage
        $config = $this->getLoggedInUser()->getConfig();
        $config->setActionMarkAsRead(Config::REDIRECT_TO_HOMEPAGE);
        $this->getEntityManager()->persist($config);

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $this->getEntityManager()->persist($entry);

        $this->getEntityManager()->flush();

        $client->request('GET', '/view/' . $entry->getId());
        $client->request('GET', '/archive/' . $entry->getId());

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertSame('/', $client->getResponse()->headers->get('location'));
    }

    public function testRedirectToCurrentPage()
    {
        $this->logInAs('empty');
        $client = $this->getClient();

        // Redirect to current page
        $config = $this->getLoggedInUser()->getConfig();
        $config->setActionMarkAsRead(Config::REDIRECT_TO_CURRENT_PAGE);
        $this->getEntityManager()->persist($config);

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $this->getEntityManager()->persist($entry);

        $this->getEntityManager()->flush();

        $client->request('GET', '/view/' . $entry->getId());
        $client->request('GET', '/archive/' . $entry->getId());

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/view/' . $entry->getId(), $client->getResponse()->headers->get('location'));
    }

    public function testFilterOnHttpStatus()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('https://www.lemonde.fr/incorrect-url/');
        $entry->setHttpStatus(404);
        $this->getEntityManager()->persist($entry);

        $this->getEntityManager()->flush();

        $crawler = $client->request('GET', '/all/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();

        $data = [
            'entry_filter[httpStatus]' => 404,
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(1, $crawler->filter('li.entry'));

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $entry->setHttpStatus(200);
        $this->getEntityManager()->persist($entry);

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('http://www.nextinpact.com/news/101235-wallabag-alternative-libre-a-pocket-creuse-petit-a-petit-son-nid.htm');
        $entry->setHttpStatus(200);
        $this->getEntityManager()->persist($entry);

        $this->getEntityManager()->flush();

        $crawler = $client->request('GET', '/all/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();

        $data = [
            'entry_filter[httpStatus]' => 200,
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(2, $crawler->filter('li.entry'));

        $crawler = $client->request('GET', '/all/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();

        $data = [
            'entry_filter[httpStatus]' => 1024,
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(8, $crawler->filter('li.entry'));
    }

    public function testSearch()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $entry->setTitle('test');
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        // Search on unread list
        $crawler = $client->request('GET', '/unread/list');

        $form = $crawler->filter('form[name=search]')->form();
        $data = [
            'search_entry[term]' => 'title',
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(4, $crawler->filter('li.entry'));

        // Search on starred list
        $crawler = $client->request('GET', '/starred/list');

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('http://localhost/foo/bar');
        $entry->setTitle('testeur');
        $entry->setStarred(true);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $form = $crawler->filter('form[name=search]')->form();
        $data = [
            'search_entry[term]' => 'testeur',
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(1, $crawler->filter('li.entry'));

        $crawler = $client->request('GET', '/archive/list');

        // Added new article to test on archive list
        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('http://0.0.0.0/foo/baz/qux');
        $entry->setTitle('Le manège');
        $entry->updateArchived(true);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $form = $crawler->filter('form[name=search]')->form();
        $data = [
            'search_entry[term]' => 'manège',
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(1, $crawler->filter('li.entry'));
        $client->request('GET', '/delete/' . $entry->getId());

        // test on list of all articles
        $crawler = $client->request('GET', '/all/list');

        $form = $crawler->filter('form[name=search]')->form();
        $data = [
            'search_entry[term]' => 'wxcvbnqsdf', // a string not available in the database
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(0, $crawler->filter('li.entry'));

        // test url search on list of all articles
        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('http://domain/qux');
        $entry->setTitle('Le manège');
        $entry->updateArchived(true);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $crawler = $client->request('GET', '/all/list');

        $form = $crawler->filter('form[name=search]')->form();
        $data = [
            'search_entry[term]' => 'domain', // the search will match an entry with 'domain' in its url
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(1, $crawler->filter('li.entry'));

        // same as previous test but for case-sensitivity
        $crawler = $client->request('GET', '/all/list');

        $form = $crawler->filter('form[name=search]')->form();
        $data = [
            'search_entry[term]' => 'doMain', // the search will match an entry with 'domain' in its url
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(1, $crawler->filter('li.entry'));
    }

    public function dataForLanguage()
    {
        return [
            'ru' => [
                'https://www.pravda.ru/world/09-06-2017/1337283-qatar-0/',
                'ru',
            ],
            'fr' => [
                'https://fr.wikipedia.org/wiki/Wallabag',
                'fr',
            ],
            'de' => [
                'https://www.bild.de/politik/ausland/theresa-may/wahlbeben-grossbritannien-analyse-52108924.bild.html',
                'de',
            ],
            'it' => [
                'https://www.ansa.it/sito/notizie/mondo/europa/2017/06/08/voto-gb-seggi-aperti-misure-sicurezza-rafforzate_0cb71f7f-e23b-4d5f-95ca-bc12296419f0.html',
                'it',
            ],
            'zh_CN' => [
                'http://www.hao123.com/shequ?__noscript__-=1',
                'zh_CN',
            ],
            'pt_BR' => [
                'https://politica.estadao.com.br/noticias/eleicoes,campanha-catatonica,70002491983',
                'pt_BR',
            ],
            'fucked_list_of_languages' => [
                'http://geocatalog.webservice-energy.org/geonetwork/srv/eng/main.home',
                null,
            ],
            'es-ES' => [
                'https://www.20minutos.es/noticia/3360685/0/gobierno-sanchez-primero-historia-mas-mujeres-que-hombres/',
                'es_ES',
            ],
        ];
    }

    /**
     * @dataProvider dataForLanguage
     * @group NetworkCalls
     */
    public function testLanguageValidation($url, $expectedLanguage)
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $url,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId($url, $this->getLoggedInUserId());

        $this->assertInstanceOf('Wallabag\CoreBundle\Entity\Entry', $content);
        $this->assertSame($url, $content->getUrl());
        $this->assertSame($expectedLanguage, $content->getLanguage());
    }

    /**
     * @group NetworkCalls
     */
    public function testRestrictedArticle()
    {
        $url = 'https://www.monde-diplomatique.fr/2017/05/BONNET/57475';
        $this->logInAs('admin');
        $client = $this->getClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');

        // enable restricted access
        $client->getContainer()->get('craue_config')->set('restricted_access', 1);

        // create a new site_credential
        $user = $client->getContainer()->get('security.token_storage')->getToken()->getUser();
        $credential = new SiteCredential($user);
        $credential->setHost('monde-diplomatique.fr');
        $credential->setUsername($client->getContainer()->get('wallabag_core.helper.crypto_proxy')->crypt('foo'));
        $credential->setPassword($client->getContainer()->get('wallabag_core.helper.crypto_proxy')->crypt('bar'));

        $em->persist($credential);
        $em->flush();

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $url,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('flashes.entry.notice.entry_saved', $crawler->filter('body')->extract(['_text'])[0]);

        $content = $em
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId($url, $this->getLoggedInUserId());

        $this->assertInstanceOf('Wallabag\CoreBundle\Entity\Entry', $content);
        $this->assertSame('Crimes et réformes aux Philippines', $content->getTitle());

        $client->getContainer()->get('craue_config')->set('restricted_access', 0);
    }

    public function testPostEntryWhenFetchFails()
    {
        $url = 'http://example.com/papers/email_tracking.pdf';
        $this->logInAs('admin');
        $client = $this->getClient();

        $container = $client->getContainer();
        $contentProxy = $this->getMockBuilder(ContentProxy::class)
            ->disableOriginalConstructor()
            ->setMethods(['updateEntry'])
            ->getMock();
        $contentProxy->expects($this->any())
            ->method('updateEntry')
            ->willThrowException(new \Exception('Test Fetch content fails'));

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $url,
        ];

        /**
         * We generate a new client to be able to use Mock ContentProxy
         * Also we reinject the cookie from the previous client to keep the
         * session.
         */
        $cookie = $client->getCookieJar()->all();
        $client = $this->getNewClient();
        $client->getCookieJar()->set($cookie[0]);
        $client->getContainer()->set('wallabag_core.content_proxy', $contentProxy);
        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId($url, $this->getLoggedInUserId());

        $authors = $content->getPublishedBy();
        $this->assertSame('email_tracking.pdf', $content->getTitle());
        $this->assertSame('example.com', $content->getDomainName());
    }

    public function testEntryDeleteTagLink()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $entry = $em->getRepository('WallabagCoreBundle:Entry')->findByUrlAndUserId('http://0.0.0.0/entry1', $this->getLoggedInUserId());
        $tag = $entry->getTags()[0];

        $crawler = $client->request('GET', '/view/' . $entry->getId());

        // As long as the deletion link of a tag is following
        // a link to the tag view, we take the second one to retrieve
        // the deletion link of the first tag
        $link = $crawler->filter('body div#article div.tools ul.tags li.chip a')->extract('href')[1];

        $this->assertSame(sprintf('/remove-tag/%s/%s', $entry->getId(), $tag->getId()), $link);
    }

    public function testRandom()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/unread/random');
        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/view/', $client->getResponse()->getTargetUrl(), 'Unread random');

        $client->request('GET', '/starred/random');
        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/view/', $client->getResponse()->getTargetUrl(), 'Starred random');

        $client->request('GET', '/archive/random');
        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/view/', $client->getResponse()->getTargetUrl(), 'Archive random');

        $client->request('GET', '/untagged/random');
        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/view/', $client->getResponse()->getTargetUrl(), 'Untagged random');

        $client->request('GET', '/all/random');
        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/view/', $client->getResponse()->getTargetUrl(), 'All random');
    }

    public function testMass()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $entry1 = new Entry($this->getLoggedInUser());
        $entry1->setUrl($this->url);
        $this->getEntityManager()->persist($entry1);

        $entry2 = new Entry($this->getLoggedInUser());
        $entry2->setUrl($this->url);
        $this->getEntityManager()->persist($entry2);

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $entries = [];
        $entries[] = $entry1->getId();
        $entries[] = $entry2->getId();

        // Mass actions : archive
        $client->request('POST', '/mass', [
            'toggle-archive' => '',
            'entry-checkbox' => $entries,
        ]);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $res = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->find($entry1->getId());

        $this->assertSame(1, $res->isArchived());

        $res = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->find($entry2->getId());

        $this->assertSame(1, $res->isArchived());

        // Mass actions : star
        $client->request('POST', '/mass', [
            'toggle-star' => '',
            'entry-checkbox' => $entries,
        ]);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $res = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->find($entry1->getId());

        $this->assertSame(1, $res->isStarred());

        $res = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->find($entry2->getId());

        $this->assertSame(1, $res->isStarred());

        // Mass actions : delete
        $client->request('POST', '/mass', [
            'delete' => '',
            'entry-checkbox' => $entries,
        ]);

        $client->request('GET', '/delete/' . $entry1->getId());
        $this->assertSame(404, $client->getResponse()->getStatusCode());

        $client->request('GET', '/delete/' . $entry2->getId());
        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }
}
