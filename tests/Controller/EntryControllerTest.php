<?php

namespace Tests\Wallabag\Controller;

use Craue\ConfigBundle\Util\Config;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\Annotation;
use Wallabag\Entity\Config as ConfigEntity;
use Wallabag\Entity\Entry;
use Wallabag\Entity\SiteCredential;
use Wallabag\Entity\Tag;
use Wallabag\Entity\User;
use Wallabag\Helper\ContentProxy;
use Wallabag\Helper\CryptoProxy;

class EntryControllerTest extends WallabagTestCase
{
    public const AN_URL_CONTAINING_AN_ARTICLE_WITH_IMAGE = 'https://www.20minutes.fr/sport/jo_2024/4095122-20240712-jo-paris-2024-saut-ange-bombe-comment-anne-hidalgo-va-plonger-seine-si-fait-vraiment';
    public $downloadImagesEnabled = false;
    public $url = 'https://www.20minutes.fr/sport/jo_2024/4095122-20240712-jo-paris-2024-saut-ange-bombe-comment-anne-hidalgo-va-plonger-seine-si-fait-vraiment';
    public $wrongUrl = 'wallabagIsAwesome';
    private $entryDataTestAttribute = '[data-test="entry"]';

    /**
     * @after
     *
     * Ensure download_images_enabled is disabled after each script
     */
    public function tearDownImagesEnabled()
    {
        if ($this->downloadImagesEnabled) {
            $client = static::createClient();
            $client->getContainer()->get(Config::class)->set('download_images_enabled', '0');

            $this->downloadImagesEnabled = false;
        }
    }

    public function testLogin()
    {
        $client = $this->getTestClient();

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
        $client = $this->getTestClient();

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
        $client = $this->getTestClient();

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
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/');

        $this->assertCount(5, $crawler->filter($this->entryDataTestAttribute));

        // Good URL
        $client->request('GET', '/bookmarklet', ['url' => $this->url]);
        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        $crawler = $client->request('GET', '/');
        $this->assertCount(6, $crawler->filter($this->entryDataTestAttribute));

        $em = $client->getContainer()
            ->get(EntityManagerInterface::class);
        $entry = $em
            ->getRepository(Entry::class)
            ->findByUrlAndUserId($this->url, $this->getLoggedInUserId());
        $em->remove($entry);
        $em->flush();
    }

    public function testPostNewEmpty()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $crawler = $client->submit($form);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
    }

    /**
     * @group NetworkCalls
     */
    public function testPostNewOk()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->getContainer()->get(Config::class)->set('store_article_headers', 1);

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $this->url,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $content = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId($this->url, $this->getLoggedInUserId());

        $author = $content->getPublishedBy();

        $this->assertInstanceOf(Entry::class, $content);
        $this->assertSame($this->url, $content->getUrl());
        $this->assertStringContainsString('Comment Hidalgo', $content->getTitle());
        $this->assertSame('fr', $content->getLanguage());
        $this->assertArrayHasKey('cache-control', $content->getHeaders());
        $client->getContainer()->get(Config::class)->set('store_article_headers', 0);
    }

    /**
     * @group NetworkCalls
     */
    public function testPostNewOkWithTaggingRules()
    {
        $this->logInAs('empty');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $this->url,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $content = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId($this->url, $this->getLoggedInUserId());

        $tags = $content->getTagsLabel();

        /*
         * Without the custom reading speed of `empty` user, it'll be inversed
         */
        $this->assertContains('longread', $tags);
        $this->assertNotContains('shortread', $tags);
    }

    /**
     * @group NetworkCalls
     */
    public function testPostWithMultipleAuthors()
    {
        $url = 'https://www.liberation.fr/planete/2017/04/05/donald-trump-et-xi-jinping-tentative-de-flirt-en-floride_1560768';
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $url,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $content = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId($url, $this->getLoggedInUserId());

        $this->assertInstanceOf(Entry::class, $content);
        $authors = $content->getPublishedBy();
        $this->assertSame('2017-04-05', $content->getPublishedAt()->format('Y-m-d'));
        $this->assertSame('fr', $content->getLanguage());
        $this->assertStringContainsString('Balenieri', $authors[0]);
        $this->assertStringContainsString('Autran', $authors[1]);
    }

    public function testPostNewOkUrlExist()
    {
        $this->logInAs('admin');

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $client = $this->getTestClient();

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
        $client = $this->getTestClient();

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
        $client = $this->getTestClient();

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
        $client = $this->getTestClient();

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
            ->get(EntityManagerInterface::class);
        $entry = $em
            ->getRepository(Entry::class)
            ->findOneByUrl($url);
        $tags = $entry->getTagsLabel();

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
            ->getRepository(Entry::class)
            ->findOneByUrl($url);

        $tags = $entry->getTagsLabel();

        $this->assertCount(2, $tags);
        $this->assertContains('wallabag', $tags);

        $em->remove($entry);
        $em->flush();
    }

    /**
     * @group NetworkCalls
     */
    public function testBadFormatURL()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->getContainer()->get(Config::class)->set('store_article_headers', 1);

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $this->wrongUrl,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $content = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId($this->wrongUrl, $this->getLoggedInUserId());

        $this->assertFalse($content);
    }

    public function testArchive()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->request('GET', '/archive/list');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testUntagged()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->request('GET', '/untagged/list');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testStarred()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->request('GET', '/starred/list');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testWithAnnotations()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/annotated/list');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(2, $crawler->filter('ol.entries > li'));
    }

    public function testRangeException()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->request('GET', '/all/list/900');

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertSame('/all/list', $client->getResponse()->getTargetUrl());
    }

    public function testView()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

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
        $client = $this->getTestClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $entry->setTitle('title foo');
        $entry->setContent('');
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/view/' . $entry->getId());

        $client->submit($crawler->selectButton('entry.view.left_menu.re_fetch_content')->form());

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $entry = $this->getEntityManager()
            ->getRepository(Entry::class)
            ->find($entry->getId());

        $this->assertNotEmpty($entry->getContent());
    }

    public function testReloadWithFetchingFailed()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('http://0.0.0.0/failed.html');
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $crawler = $client->request('GET', '/view/' . $entry->getId());

        $client->submit($crawler->selectButton('entry.view.left_menu.re_fetch_content')->form());

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        // force EntityManager to clear previous entity
        // otherwise, retrieve the same entity will retrieve change from the previous request :0
        $this->getEntityManager()->clear();
        $newContent = $this->getEntityManager()
            ->getRepository(Entry::class)
            ->find($entry->getId());

        $this->assertNotSame($client->getContainer()->getParameter('wallabag.fetching_error_message'), $newContent->getContent());
    }

    public function testEdit()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

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
        $client = $this->getTestClient();

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
        $originUrl = $crawler->filter('[data-tests="entry-origin-url"]')->text();
        $this->assertStringContainsString('example.io', $originUrl);
    }

    public function testEditRemoveOriginUrl()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

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

        $originUrl = $crawler->filter('[data-tests="entry-origin-url"]')->extract(['_text']);
        $this->assertCount(0, $originUrl);
    }

    public function testToggleArchive()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/view/' . $entry->getId());

        $client->submit($crawler->filter('.left-bar')->selectButton('entry.view.left_menu.set_as_read')->form());

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $res = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->find($entry->getId());

        $this->assertTrue($res->isArchived());
    }

    public function testToggleStar()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/view/' . $entry->getId());

        $client->submit($crawler->filter('.left-bar')->selectButton('entry.view.left_menu.set_as_starred')->form());

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $res = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneById($entry->getId());

        $this->assertTrue($res->isStarred());
    }

    public function testDelete()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $crawler = $client->request('POST', '/delete/' . $entry->getId());

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('Bad CSRF token.', $body[0]);
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
        $client = $this->getTestClient();

        $em = $client->getContainer()
            ->get(EntityManagerInterface::class);

        // add a new content to be removed later
        $user = $em
            ->getRepository(User::class)
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

        $crawler = $client->request('GET', '/view/' . $content->getId());
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $client->submit($crawler->filter('.left-bar')->selectButton('entry.view.left_menu.delete')->form());

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $client->followRedirect();
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testViewOtherUserEntry()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $content = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneByUsernameAndNotArchived('bob');

        $client->request('GET', '/view/' . $content->getId());

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testFilterOnReadingTime()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
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

        $this->assertCount(1, $crawler->filter($this->entryDataTestAttribute));
    }

    public function testFilterOnReadingTimeWithNegativeValue()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/unread/list');

        $form = $crawler->filter('button[id=submit-filter]')->form();

        $data = [
            'entry_filter[readingTime][right_number]' => -22,
            'entry_filter[readingTime][left_number]' => -22,
        ];

        $crawler = $client->submit($form, $data);

        // forcing negative value results in no entry displayed
        $this->assertCount(0, $crawler->filter($this->entryDataTestAttribute));
    }

    public function testFilterOnReadingTimeOnlyUpper()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/all/list');
        $this->assertCount(6, $crawler->filter($this->entryDataTestAttribute));

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $entry->setReadingTime(23);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $crawler = $client->request('GET', '/all/list');
        $this->assertCount(7, $crawler->filter($this->entryDataTestAttribute));

        $form = $crawler->filter('button[id=submit-filter]')->form();

        $data = [
            'entry_filter[readingTime][right_number]' => 22,
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(6, $crawler->filter($this->entryDataTestAttribute));
    }

    public function testFilterOnReadingTimeOnlyLower()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/unread/list');

        $form = $crawler->filter('button[id=submit-filter]')->form();

        $data = [
            'entry_filter[readingTime][left_number]' => 22,
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(0, $crawler->filter($this->entryDataTestAttribute));

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $entry->setReadingTime(23);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $crawler = $client->submit($form, $data);
        $this->assertCount(1, $crawler->filter($this->entryDataTestAttribute));
    }

    public function testFilterOnUnreadStatus()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/all/list');

        $form = $crawler->filter('button[id=submit-filter]')->form();

        $data = [
            'entry_filter[isUnread]' => true,
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(5, $crawler->filter($this->entryDataTestAttribute));

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $entry->updateArchived(false);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $crawler = $client->submit($form, $data);

        $this->assertCount(6, $crawler->filter($this->entryDataTestAttribute));
    }

    public function testFilterOnCreationDate()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

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

        $this->assertCount(5, $crawler->filter($this->entryDataTestAttribute));

        $data = [
            'entry_filter[createdAt][left_date]' => $today->format('Y-m-d'),
            'entry_filter[createdAt][right_date]' => $today->format('Y-m-d'),
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(5, $crawler->filter($this->entryDataTestAttribute));

        $data = [
            'entry_filter[createdAt][left_date]' => '1970-01-01',
            'entry_filter[createdAt][right_date]' => '1970-01-01',
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(0, $crawler->filter($this->entryDataTestAttribute));
    }

    public function testFilterOnAnnotatedStatus()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/all/list');

        $form = $crawler->filter('button[id=submit-filter]')->form();

        $data = [
            'entry_filter[isAnnotated]' => true,
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(2, $crawler->filter('ol.entries > li'));

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);

        $em = $this->getTestClient()->getContainer()->get(EntityManagerInterface::class);
        $user = $em
            ->getRepository(User::class)
            ->findOneByUserName('admin');

        $annotation = new Annotation($user);
        $annotation->setEntry($entry);
        $annotation->setText('This is my annotation /o/');
        $annotation->setQuote('content');

        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $crawler = $client->submit($form, $data);

        $this->assertCount(3, $crawler->filter('ol.entries > li'));
    }

    public function testFilterOnNotCorrectlyParsedStatus()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/all/list');

        $form = $crawler->filter('button[id=submit-filter]')->form();

        $data = [
            'entry_filter[isNotParsed]' => true,
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(1, $crawler->filter($this->entryDataTestAttribute));

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $entry->setNotParsed(true);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $crawler = $client->submit($form, $data);

        $this->assertCount(2, $crawler->filter($this->entryDataTestAttribute));
    }

    public function testPaginationWithFilter()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
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
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/unread/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();
        $data = [
            'entry_filter[domainName]' => 'domain',
        ];

        $crawler = $client->submit($form, $data);
        $this->assertCount(4, $crawler->filter($this->entryDataTestAttribute));

        $crawler = $client->request('GET', '/unread/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();
        $data = [
            'entry_filter[domainName]' => 'dOmain',
        ];

        $crawler = $client->submit($form, $data);
        $this->assertCount(4, $crawler->filter($this->entryDataTestAttribute));

        $form = $crawler->filter('button[id=submit-filter]')->form();
        $data = [
            'entry_filter[domainName]' => 'wallabag',
        ];

        $crawler = $client->submit($form, $data);
        $this->assertCount(0, $crawler->filter($this->entryDataTestAttribute));
    }

    public function testFilterOnStatus()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/unread/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();
        $form['entry_filter[isArchived]']->tick();
        $form['entry_filter[isStarred]']->untick();
        $form['entry_filter[isUnread]']->untick();

        $crawler = $client->submit($form);
        $this->assertCount(1, $crawler->filter($this->entryDataTestAttribute));

        $form = $crawler->filter('button[id=submit-filter]')->form();
        $form['entry_filter[isArchived]']->untick();
        $form['entry_filter[isStarred]']->tick();

        $crawler = $client->submit($form);
        $this->assertCount(1, $crawler->filter($this->entryDataTestAttribute));
    }

    public function testFilterPreselectedStatus()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/unread/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();
        $this->assertTrue($form['entry_filter[isUnread]']->hasValue());
        $this->assertFalse($form['entry_filter[isArchived]']->hasValue());
        $this->assertFalse($form['entry_filter[isStarred]']->hasValue());

        $crawler = $client->request('GET', '/starred/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();
        $this->assertFalse($form['entry_filter[isUnread]']->hasValue());
        $this->assertFalse($form['entry_filter[isArchived]']->hasValue());
        $this->assertTrue($form['entry_filter[isStarred]']->hasValue());

        $crawler = $client->request('GET', '/all/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();
        $this->assertFalse($form['entry_filter[isUnread]']->hasValue());
        $this->assertFalse($form['entry_filter[isArchived]']->hasValue());
        $this->assertFalse($form['entry_filter[isStarred]']->hasValue());
    }

    public function testFilterOnIsPublic()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/unread/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();
        $form['entry_filter[isPublic]']->tick();

        $crawler = $client->submit($form);
        $this->assertCount(0, $crawler->filter($this->entryDataTestAttribute));
    }

    public function testPreviewPictureFilter()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/unread/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();
        $form['entry_filter[previewPicture]']->tick();

        $crawler = $client->submit($form);
        $this->assertCount(1, $crawler->filter($this->entryDataTestAttribute));
    }

    public function testFilterOnLanguage()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

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
        $this->assertCount(3, $crawler->filter($this->entryDataTestAttribute));

        $form = $crawler->filter('button[id=submit-filter]')->form();
        $data = [
            'entry_filter[language]' => 'en',
        ];

        $crawler = $client->submit($form, $data);
        $this->assertCount(2, $crawler->filter($this->entryDataTestAttribute));
    }

    public function testShareEntryPublicly()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        // sharing is enabled
        $client->getContainer()->get(Config::class)->set('share_public', 1);

        $content = new Entry($this->getLoggedInUser());
        $content->setUrl($this->url);
        $this->getEntityManager()->persist($content);
        $this->getEntityManager()->flush();

        $client = $this->getTestClient();

        // no uid
        $client->request('GET', '/share/' . $content->getUid());
        $this->assertSame(404, $client->getResponse()->getStatusCode());

        // generating the uid
        $crawler = $client->request('GET', '/view/' . $content->getId());

        $client->submit($crawler->filter('.left-bar')->selectButton('entry.view.left_menu.public_link')->form());

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $shareUrl = $client->getResponse()->getTargetUrl();

        // use a new client to have a fresh empty session (instead of a logged one from the previous client)
        $client->restart();

        $client->request('GET', $shareUrl);

        // @TODO: understand why public & max-age are override after the response is return in the controller
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        // $this->assertStringContainsString('max-age=25200', $client->getResponse()->headers->get('cache-control'));
        // $this->assertStringContainsString('public', $client->getResponse()->headers->get('cache-control'));
        $this->assertStringContainsString('s-maxage=25200', $client->getResponse()->headers->get('cache-control'));
        // $this->assertStringNotContainsString('no-cache', $client->getResponse()->headers->get('cache-control'));
        $this->assertStringContainsString('og:title', $client->getResponse()->getContent());
        $this->assertStringContainsString('og:type', $client->getResponse()->getContent());
        $this->assertStringContainsString('og:url', $client->getResponse()->getContent());
        $this->assertStringContainsString('og:image', $client->getResponse()->getContent());

        // sharing is now disabled
        $client->getContainer()->get(Config::class)->set('share_public', 0);
        $client->request('GET', '/share/' . $content->getUid());
        $this->assertSame(404, $client->getResponse()->getStatusCode());

        // removing the share
        $client->getContainer()->get(Config::class)->set('share_public', 1);
        $this->logInAs('admin');
        $crawler = $client->request('GET', '/view/' . $content->getId());

        $client->submit($crawler->filter('.left-bar')->selectButton('entry.view.left_menu.delete_public_link')->form());

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        // share is now removed
        $client->request('GET', '/share/' . $content->getUid());
        $this->assertSame(404, $client->getResponse()->getStatusCode());

        $client->getContainer()->get(Config::class)->set('share_public', 0);
    }

    /**
     * @group NetworkCalls
     */
    public function testNewEntryWithDownloadImagesEnabled()
    {
        $this->downloadImagesEnabled = true;
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $url = self::AN_URL_CONTAINING_AN_ARTICLE_WITH_IMAGE;
        $client->getContainer()->get(Config::class)->set('download_images_enabled', 1);

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $url,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $em = $client->getContainer()
            ->get(EntityManagerInterface::class);

        $entry = $em
            ->getRepository(Entry::class)
            ->findByUrlAndUserId($url, $this->getLoggedInUserId());

        $this->assertInstanceOf(Entry::class, $entry);
        $this->assertSame($url, $entry->getUrl());
        $this->assertStringContainsString('Comment Hidalgo', $entry->getTitle());
        // instead of checking for the filename (which might change) check that the image is now local
        $this->assertStringContainsString(rtrim((string) $client->getContainer()->getParameter('domain_name'), '/') . '/assets/images/', $entry->getContent());

        $client->getContainer()->get(Config::class)->set('download_images_enabled', 0);
    }

    /**
     * @depends testNewEntryWithDownloadImagesEnabled
     */
    public function testRemoveEntryWithDownloadImagesEnabled()
    {
        $this->downloadImagesEnabled = true;
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $url = self::AN_URL_CONTAINING_AN_ARTICLE_WITH_IMAGE;
        $client->getContainer()->get(Config::class)->set('download_images_enabled', 1);

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $url,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $content = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId($url, $this->getLoggedInUserId());

        $crawler = $client->request('GET', '/view/' . $content->getId());

        $client->submit($crawler->filter('.left-bar')->selectButton('entry.view.left_menu.delete')->form());

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $client->getContainer()->get(Config::class)->set('download_images_enabled', 0);
    }

    public function testRedirectToHomepage()
    {
        $this->logInAs('empty');
        $client = $this->getTestClient();

        // Redirect to homepage
        $config = $this->getLoggedInUser()->getConfig();
        $config->setActionMarkAsRead(ConfigEntity::REDIRECT_TO_HOMEPAGE);
        $this->getEntityManager()->persist($config);

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $this->getEntityManager()->persist($entry);

        $this->getEntityManager()->flush();

        $crawler = $client->request('GET', '/view/' . $entry->getId());

        $client->submit($crawler->filter('.left-bar')->selectButton('entry.view.left_menu.set_as_read')->form());

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertSame('/', $client->getResponse()->headers->get('location'));
    }

    public function testRedirectToCurrentPage()
    {
        $this->logInAs('empty');
        $client = $this->getTestClient();

        // Redirect to current page
        $config = $this->getLoggedInUser()->getConfig();
        $config->setActionMarkAsRead(ConfigEntity::REDIRECT_TO_CURRENT_PAGE);
        $this->getEntityManager()->persist($config);

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $this->getEntityManager()->persist($entry);

        $this->getEntityManager()->flush();

        $crawler = $client->request('GET', '/view/' . $entry->getId());

        $client->submit($crawler->filter('.left-bar')->selectButton('entry.view.left_menu.set_as_read')->form());

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/view/' . $entry->getId(), $client->getResponse()->headers->get('location'));
    }

    public function testFilterOnHttpStatus()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('https://www.lemonde.fr/incorrect-url/');
        $entry->setHttpStatus('404');
        $this->getEntityManager()->persist($entry);

        $this->getEntityManager()->flush();

        $crawler = $client->request('GET', '/all/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();

        $data = [
            'entry_filter[httpStatus]' => 404,
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(1, $crawler->filter($this->entryDataTestAttribute));

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $entry->setHttpStatus('200');
        $this->getEntityManager()->persist($entry);

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('http://www.nextinpact.com/news/101235-wallabag-alternative-libre-a-pocket-creuse-petit-a-petit-son-nid.htm');
        $entry->setHttpStatus('200');
        $this->getEntityManager()->persist($entry);

        $this->getEntityManager()->flush();

        $crawler = $client->request('GET', '/all/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();

        $data = [
            'entry_filter[httpStatus]' => 200,
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(2, $crawler->filter($this->entryDataTestAttribute));

        $crawler = $client->request('GET', '/all/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();

        $data = [
            'entry_filter[httpStatus]' => 1024,
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(9, $crawler->filter($this->entryDataTestAttribute));
    }

    public function testSearch()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

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

        $this->assertCount(5, $crawler->filter($this->entryDataTestAttribute));

        // Add a check with useless spaces before and after the search term
        $crawler = $client->request('GET', '/unread/list');

        $form = $crawler->filter('form[name=search]')->form();
        $data = [
            'search_entry[term]' => '  title ',
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(5, $crawler->filter($this->entryDataTestAttribute));

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

        $this->assertCount(1, $crawler->filter($this->entryDataTestAttribute));

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

        $this->assertCount(1, $crawler->filter($this->entryDataTestAttribute));

        $client->submit($crawler->filter('.tools, .tools-list')->selectButton('delete')->form());

        // test on list of all articles
        $crawler = $client->request('GET', '/all/list');

        $form = $crawler->filter('form[name=search]')->form();
        $data = [
            'search_entry[term]' => 'wxcvbnqsdf', // a string not available in the database
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(0, $crawler->filter($this->entryDataTestAttribute));

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

        $this->assertCount(1, $crawler->filter($this->entryDataTestAttribute));

        // same as previous test but for case-sensitivity
        $crawler = $client->request('GET', '/all/list');

        $form = $crawler->filter('form[name=search]')->form();
        $data = [
            'search_entry[term]' => 'doMain', // the search will match an entry with 'domain' in its url
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(1, $crawler->filter($this->entryDataTestAttribute));

        $crawler = $client->request('GET', '/unread/list');

        $form = $crawler->filter('form[name=search]')->form();
        $data = [
            'search_entry[term]' => 'annotation',
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(2, $crawler->filter($this->entryDataTestAttribute));
    }

    public function testActionInSearchResults()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $config = $this->getLoggedInUser()->getConfig();
        $config->setActionMarkAsRead(ConfigEntity::REDIRECT_TO_CURRENT_PAGE);
        $this->getEntityManager()->persist($config);

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($this->url);
        $entry->setTitle('ActionInSearchResults');
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        // Search on unread list
        $crawler = $client->request('GET', '/unread/list');

        $form = $crawler->filter('form[name=search]')->form();
        $data = [
            'search_entry[term]' => 'ActionInSearchResults',
        ];

        $crawler = $client->submit($form, $data);
        $currentUrl = $client->getRequest()->getUri();
        $form = $crawler->filter('.tools, .tools-list')->selectButton('delete')->form();
        $client->submit($form);
        $client->followRedirect();
        $nextUrl = $client->getRequest()->getUri();
        $this->assertSame($currentUrl, $nextUrl);
    }

    public function dataForLanguage()
    {
        return [
            'ru' => [
                'https://ru.wikipedia.org/wiki/%D0%A0%D1%83%D1%81%D1%81%D0%BA%D0%B8%D0%B9_%D1%8F%D0%B7%D1%8B%D0%BA',
                'ru',
            ],
            'fr' => [
                'https://fr.wikipedia.org/wiki/Wallabag',
                'fr',
            ],
            'de' => [
                'https://de.wikipedia.org/wiki/Deutsche_Sprache',
                'de',
            ],
            'it' => [
                'https://it.wikipedia.org/wiki/Lingua_italiana',
                'it',
            ],
            'zh' => [
                'https://zh.wikipedia.org/wiki/%E7%8F%BE%E4%BB%A3%E6%A8%99%E6%BA%96%E6%BC%A2%E8%AA%9E',
                'zh',
            ],
            'pt_BR' => [
                'https://www.monpetitbresil.com/pages/quem-somos',
                'pt_BR',
            ],
            'es' => [
                'https://es.wikipedia.org/wiki/Idioma_espa%C3%B1ol',
                'es',
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
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $url,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $content = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId($url, $this->getLoggedInUserId());

        $this->assertInstanceOf(Entry::class, $content);
        $this->assertSame($url, $content->getUrl());
        $this->assertSame($expectedLanguage, $content->getLanguage());
    }

    /**
     * @group NetworkCalls
     */
    public function testRestrictedArticle()
    {
        $url = 'https://www.monde-diplomatique.fr/2017/05/BONNET/57476';
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        // enable restricted access
        $client->getContainer()->get(Config::class)->set('restricted_access', 1);

        // create a new site_credential
        $user = $client->getContainer()->get(TokenStorageInterface::class)->getToken()->getUser();
        $credential = new SiteCredential($user);
        $credential->setHost('monde-diplomatique.fr');
        $credential->setUsername($client->getContainer()->get(CryptoProxy::class)->crypt('foo'));
        $credential->setPassword($client->getContainer()->get(CryptoProxy::class)->crypt('bar'));

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
            ->getRepository(Entry::class)
            ->findByUrlAndUserId($url, $this->getLoggedInUserId());

        $this->assertInstanceOf(Entry::class, $content);
        $this->assertSame('Quand Manille manœuvre', $content->getTitle());

        $client->getContainer()->get(Config::class)->set('restricted_access', 0);
    }

    public function testPostEntryWhenFetchFails()
    {
        $url = 'http://example.com/papers/email_tracking.pdf';
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $container = $client->getContainer();
        $contentProxy = $this->getMockBuilder(ContentProxy::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['updateEntry'])
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
        $client->getContainer()->set(ContentProxy::class, $contentProxy);
        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $content = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId($url, $this->getLoggedInUserId());

        $authors = $content->getPublishedBy();
        $this->assertSame('email_tracking.pdf', $content->getTitle());
        $this->assertSame('example.com', $content->getDomainName());
    }

    public function testEntryDeleteTagForm()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $entry = $em->getRepository(Entry::class)->findByUrlAndUserId('http://0.0.0.0/entry1', $this->getLoggedInUserId());
        $tag = $entry->getTags()[0];

        $crawler = $client->request('GET', '/view/' . $entry->getId());

        $link = $crawler->filter('body div#article div.tools ul.tags li.chip form')->extract(['action'])[0];

        $this->assertStringStartsWith(\sprintf('/remove-tag/%s/%s', $entry->getId(), $tag->getId()), $link);
    }

    public function testRandom()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

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

        $client->request('GET', '/annotated/random');
        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/view/', $client->getResponse()->getTargetUrl(), 'With annotations random');

        $client->request('GET', '/all/random');
        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/view/', $client->getResponse()->getTargetUrl(), 'All random');
    }

    public function testMass()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $entry1 = new Entry($this->getLoggedInUser());
        $entry1->setUrl($this->url);
        $this->getEntityManager()->persist($entry1);

        $entry2 = new Entry($this->getLoggedInUser());
        $entry2->setUrl($this->url);
        $this->getEntityManager()->persist($entry2);

        $entry3 = new Entry($this->getLoggedInUser());
        $entry3->setUrl($this->url);
        $this->getEntityManager()->persist($entry3);

        $this->getEntityManager()->flush();

        $client = $this->getTestClient();

        $entries = [];
        $entries[] = $entry1Id = $entry1->getId();
        $entries[] = $entry2Id = $entry2->getId();

        $crawler = $client->request('GET', '/all/list');
        $token = $crawler->filter('#form_mass_action input[name=token]')->attr('value');

        // Mass actions : archive
        $client->request('POST', '/mass', [
            'token' => $token,
            'toggle-archive' => '',
            'entry-checkbox' => $entries,
        ]);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $res = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->find($entry1->getId());

        $this->assertTrue($res->isArchived());

        $res = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->find($entry2->getId());

        $this->assertTrue($res->isArchived());

        $crawler = $client->request('GET', '/all/list');
        $token = $crawler->filter('#form_mass_action input[name=token]')->attr('value');

        // Mass actions : star
        $client->request('POST', '/mass', [
            'token' => $token,
            'toggle-star' => '',
            'entry-checkbox' => $entries,
        ]);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $res = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->find($entry1->getId());

        $this->assertTrue($res->isStarred());

        $res = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->find($entry2->getId());

        $this->assertTrue($res->isStarred());

        $crawler = $client->request('GET', '/all/list');
        $token = $crawler->filter('#form_mass_action input[name=token]')->attr('value');

        // Mass actions : tag
        $client->request('POST', '/mass', [
            'token' => $token,
            'tag' => '',
            'tags' => 'foo',
            'entry-checkbox' => $entries,
        ]);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $res = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->find($entry1->getId());

        $this->assertContains('foo', $res->getTagsLabel());

        $res = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->find($entry2->getId());

        $this->assertContains('foo', $res->getTagsLabel());

        $res = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->find($entry3->getId());

        $this->assertNotContains('foo', $res->getTagsLabel());

        $crawler = $client->request('GET', '/all/list');
        $token = $crawler->filter('#form_mass_action input[name=token]')->attr('value');

        // Mass actions : delete
        $client->request('POST', '/mass', [
            'token' => $token,
            'delete' => '',
            'entry-checkbox' => $entries,
        ]);

        $res = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->find($entry1Id);

        $this->assertNull($res);

        $res = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->find($entry2Id);

        $this->assertNull($res);
    }

    public function testGetSameDomainEntries()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/domain/1');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(4, $crawler->filter('ol.entries > li'));
    }
}
