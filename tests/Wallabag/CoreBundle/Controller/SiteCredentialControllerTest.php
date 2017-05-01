<?php

namespace Tests\Wallabag\CoreBundle\Controller;

use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;

class SiteCredentialControllerTest extends WallabagCoreTestCase
{
    public function testListSiteCredential()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/site-credentials/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $body = $crawler->filter('body')->extract(['_text'])[0];

        $this->assertContains('site_credential.description', $body);
        $this->assertContains('site_credential.list.create_new_one', $body);
    }

    public function testNewSiteCredential()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/site-credentials/new');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $body = $crawler->filter('body')->extract(['_text'])[0];

        $this->assertContains('site_credential.new_site_credential', $body);
        $this->assertContains('site_credential.form.back_to_list', $body);

        $form = $crawler->filter('button[id=site_credential_save]')->form();

        $data = [
            'site_credential[host]' => 'google.io',
            'site_credential[username]' => 'sergei',
            'site_credential[password]' => 'microsoft',
        ];

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertContains('flashes.site_credential.notice.added', $crawler->filter('body')->extract(['_text'])[0]);
    }

    /**
     * @depends testNewSiteCredential
     */
    public function testEditSiteCredential()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $credential = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:SiteCredential')
            ->findOneByHost('google.io');

        $crawler = $client->request('GET', '/site-credentials/'.$credential->getId().'/edit');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $body = $crawler->filter('body')->extract(['_text'])[0];

        $this->assertContains('site_credential.edit_site_credential', $body);
        $this->assertContains('site_credential.form.back_to_list', $body);

        $form = $crawler->filter('button[id=site_credential_save]')->form();

        $data = [
            'site_credential[host]' => 'google.io',
            'site_credential[username]' => 'larry',
            'site_credential[password]' => 'microsoft',
        ];

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertContains('flashes.site_credential.notice.updated', $crawler->filter('body')->extract(['_text'])[0]);
        $this->assertContains('larry', $crawler->filter('input[id=site_credential_username]')->attr('value'));
    }

    /**
     * @depends testNewSiteCredential
     */
    public function testEditFromADifferentUserSiteCredential()
    {
        $this->logInAs('bob');
        $client = $this->getClient();

        $credential = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:SiteCredential')
            ->findOneByHost('google.io');

        $client->request('GET', '/site-credentials/'.$credential->getId().'/edit');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    /**
     * @depends testNewSiteCredential
     */
    public function testDeleteSiteCredential()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $credential = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:SiteCredential')
            ->findOneByHost('google.io');

        $crawler = $client->request('GET', '/site-credentials/'.$credential->getId().'/edit');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $deleteForm = $crawler->filter('body')->selectButton('site_credential.form.delete')->form();

        $client->submit($deleteForm, []);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertContains('flashes.site_credential.notice.deleted', $crawler->filter('body')->extract(['_text'])[0]);
    }
}
