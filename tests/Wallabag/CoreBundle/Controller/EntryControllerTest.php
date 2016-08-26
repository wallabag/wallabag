<?php

namespace Tests\Wallabag\CoreBundle\Controller;

use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Wallabag\CoreBundle\Entity\Entry;

class EntryControllerTest extends WallabagCoreTestCase
{
    public $url = 'http://www.lemonde.fr/pixels/article/2015/03/28/plongee-dans-l-univers-d-ingress-le-jeu-de-google-aux-frontieres-du-reel_4601155_4408996.html';

    public function testLogin()
    {
        $client = $this->getClient();

        $client->request('GET', '/new');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertContains('login', $client->getResponse()->headers->get('location'));
    }

    public function testQuickstart()
    {
        $this->logInAs('empty');
        $client = $this->getClient();

        $client->request('GET', '/unread/list');
        $crawler = $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertContains('quickstart.intro.paragraph_1', $body[0]);

        // Test if quickstart is disabled when user has 1 entry
        $crawler = $client->request('GET', '/new');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $this->url,
        ];

        $client->submit($form, $data);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();

        $crawler = $client->request('GET', '/unread/list');
        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertContains('entry.list.number_on_the_page', $body[0]);
    }

    public function testGetNew()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/new');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertCount(1, $crawler->filter('input[type=url]'));
        $this->assertCount(1, $crawler->filter('form[name=entry]'));
    }

    public function testPostNewViaBookmarklet()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/');

        $this->assertCount(4, $crawler->filter('div[class=entry]'));

        // Good URL
        $client->request('GET', '/bookmarklet', ['url' => $this->url]);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        $crawler = $client->request('GET', '/');
        $this->assertCount(5, $crawler->filter('div[class=entry]'));

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

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $crawler = $client->submit($form);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $alert = $crawler->filter('form ul li')->extract(['_text']));
        $this->assertEquals('This value should not be blank.', $alert[0]);
    }

    /**
     * This test will require an internet connection.
     */
    public function testPostNewOk()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/new');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $this->url,
        ];

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId($this->url, $this->getLoggedInUserId());

        $this->assertInstanceOf('Wallabag\CoreBundle\Entity\Entry', $content);
        $this->assertEquals($this->url, $content->getUrl());
        $this->assertContains('Google', $content->getTitle());
    }

    public function testPostNewOkUrlExist()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/new');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $this->url,
        ];

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertContains('/view/', $client->getResponse()->getTargetUrl());
    }

    /**
     * This test will require an internet connection.
     */
    public function testPostNewThatWillBeTagged()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/new');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $url = 'https://github.com/wallabag/wallabag',
        ];

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertContains('/', $client->getResponse()->getTargetUrl());

        $em = $client->getContainer()
            ->get('doctrine.orm.entity_manager');
        $entry = $em
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUrl($url);
        $tags = $entry->getTags();

        $this->assertCount(1, $tags);
        $this->assertEquals('wallabag', $tags[0]->getLabel());

        $em->remove($entry);
        $em->flush();

        // and now re-submit it to test the cascade persistence for tags after entry removal
        // related https://github.com/wallabag/wallabag/issues/2121
        $crawler = $client->request('GET', '/new');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();

        $data = [
            'entry[url]' => $url = 'https://github.com/wallabag/wallabag/tree/master',
        ];

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertContains('/', $client->getResponse()->getTargetUrl());

        $entry = $em
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUrl($url);

        $tags = $entry->getTags();

        $this->assertCount(1, $tags);
        $this->assertEquals('wallabag', $tags[0]->getLabel());

        $em->remove($entry);
        $em->flush();
    }

    public function testArchive()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/archive/list');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testUntagged()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/untagged/list');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testStarred()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/starred/list');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testRangeException()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/all/list/900');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals('/all/list', $client->getResponse()->getTargetUrl());
    }

    /**
     * @depends testPostNewOk
     */
    public function testView()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId($this->url, $this->getLoggedInUserId());

        $crawler = $client->request('GET', '/view/'.$content->getId());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertContains($content->getTitle(), $body[0]);
    }

    /**
     * @depends testPostNewOk
     *
     * This test will require an internet connection.
     */
    public function testReload()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId($this->url, $this->getLoggedInUserId());

        // empty content
        $content->setContent('');
        $client->getContainer()->get('doctrine.orm.entity_manager')->persist($content);
        $client->getContainer()->get('doctrine.orm.entity_manager')->flush();

        $client->request('GET', '/reload/'.$content->getId());

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId($this->url, $this->getLoggedInUserId());

        $this->assertNotEmpty($content->getContent());
    }

    public function testEdit()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId($this->url, $this->getLoggedInUserId());

        $crawler = $client->request('GET', '/edit/'.$content->getId());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertCount(1, $crawler->filter('input[id=entry_title]'));
        $this->assertCount(1, $crawler->filter('button[id=entry_save]'));
    }

    public function testEditUpdate()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId($this->url, $this->getLoggedInUserId());

        $crawler = $client->request('GET', '/edit/'.$content->getId());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[type=submit]')->form();

        $data = [
            'entry[title]' => 'My updated title hehe :)',
        ];

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $alert = $crawler->filter('div[id=article] h1')->extract(['_text']));
        $this->assertContains('My updated title hehe :)', $alert[0]);
    }

    public function testToggleArchive()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId($this->url, $this->getLoggedInUserId());

        $client->request('GET', '/archive/'.$content->getId());

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $res = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->find($content->getId());

        $this->assertEquals($res->isArchived(), true);
    }

    public function testToggleStar()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId($this->url, $this->getLoggedInUserId());

        $client->request('GET', '/star/'.$content->getId());

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $res = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneById($content->getId());

        $this->assertEquals($res->isStarred(), true);
    }

    public function testDelete()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId($this->url, $this->getLoggedInUserId());

        $client->request('GET', '/delete/'.$content->getId());

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $client->request('GET', '/delete/'.$content->getId());

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
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

        // add a new content to be removed later
        $user = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagUserBundle:User')
            ->findOneByUserName('admin');

        $content = new Entry($user);
        $content->setUrl('http://1.1.1.1/entry');
        $content->setReadingTime(12);
        $content->setDomainName('domain.io');
        $content->setMimetype('text/html');
        $content->setTitle('test title entry');
        $content->setContent('This is my content /o/');
        $content->setArchived(true);
        $content->setLanguage('fr');

        $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->persist($content);
        $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->flush();

        $client->request('GET', '/view/'.$content->getId());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/delete/'.$content->getId());
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testViewOtherUserEntry()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUsernameAndNotArchived('bob');

        $client->request('GET', '/view/'.$content->getId());

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testFilterOnReadingTime()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/unread/list');

        $form = $crawler->filter('button[id=submit-filter]')->form();

        $data = [
            'entry_filter[readingTime][right_number]' => 22,
            'entry_filter[readingTime][left_number]' => 22,
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(1, $crawler->filter('div[class=entry]'));
    }

    public function testFilterOnReadingTimeOnlyUpper()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/unread/list');

        $form = $crawler->filter('button[id=submit-filter]')->form();

        $data = [
            'entry_filter[readingTime][right_number]' => 22,
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(2, $crawler->filter('div[class=entry]'));
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

        $this->assertCount(4, $crawler->filter('div[class=entry]'));
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

        $this->assertCount(4, $crawler->filter('div[class=entry]'));
    }

    public function testFilterOnCreationDate()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/unread/list');

        $form = $crawler->filter('button[id=submit-filter]')->form();

        $data = [
            'entry_filter[createdAt][left_date]' => date('d/m/Y'),
            'entry_filter[createdAt][right_date]' => date('d/m/Y', strtotime('+1 day')),
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(5, $crawler->filter('div[class=entry]'));

        $data = [
            'entry_filter[createdAt][left_date]' => date('d/m/Y'),
            'entry_filter[createdAt][right_date]' => date('d/m/Y'),
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(5, $crawler->filter('div[class=entry]'));

        $data = [
            'entry_filter[createdAt][left_date]' => '01/01/1970',
            'entry_filter[createdAt][right_date]' => '01/01/1970',
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(0, $crawler->filter('div[class=entry]'));
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

        $client->request('GET', 'unread/list'.$parameters);

        $this->assertContains($parameters, $client->getResponse()->getContent());

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
        $this->assertCount(5, $crawler->filter('div[class=entry]'));

        $form = $crawler->filter('button[id=submit-filter]')->form();
        $data = [
            'entry_filter[domainName]' => 'wallabag',
        ];

        $crawler = $client->submit($form, $data);
        $this->assertCount(0, $crawler->filter('div[class=entry]'));
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
        $this->assertCount(1, $crawler->filter('div[class=entry]'));

        $form = $crawler->filter('button[id=submit-filter]')->form();
        $form['entry_filter[isArchived]']->untick();
        $form['entry_filter[isStarred]']->tick();

        $crawler = $client->submit($form);
        $this->assertCount(1, $crawler->filter('div[class=entry]'));
    }

    public function testPreviewPictureFilter()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/unread/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();
        $form['entry_filter[previewPicture]']->tick();

        $crawler = $client->submit($form);
        $this->assertCount(1, $crawler->filter('div[class=entry]'));
    }

    public function testFilterOnLanguage()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/unread/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();
        $data = [
            'entry_filter[language]' => 'fr',
        ];

        $crawler = $client->submit($form, $data);
        $this->assertCount(2, $crawler->filter('div[class=entry]'));

        $form = $crawler->filter('button[id=submit-filter]')->form();
        $data = [
            'entry_filter[language]' => 'en',
        ];

        $crawler = $client->submit($form, $data);
        $this->assertCount(2, $crawler->filter('div[class=entry]'));
    }

    public function testCache()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUser($this->getLoggedInUserId());

        // no uuid
        $client->request('GET', '/share/'.$content->getUuid());
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        // generating the uuid
        $client->request('GET', '/share/'.$content->getId());
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        // follow link with uuid
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('max-age=25200', $client->getResponse()->headers->get('cache-control'));
        $this->assertContains('public', $client->getResponse()->headers->get('cache-control'));
        $this->assertContains('s-maxage=25200', $client->getResponse()->headers->get('cache-control'));
        $this->assertNotContains('no-cache', $client->getResponse()->headers->get('cache-control'));

        // sharing is now disabled
        $client->getContainer()->get('craue_config')->set('share_public', 0);
        $client->request('GET', '/share/'.$content->getUuid());
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $client->request('GET', '/view/'.$content->getId());
        $this->assertContains('no-cache', $client->getResponse()->headers->get('cache-control'));

        // removing the share
        $client->request('GET', '/share/delete/'.$content->getId());
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        // share is now disable
        $client->request('GET', '/share/'.$content->getUuid());
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
