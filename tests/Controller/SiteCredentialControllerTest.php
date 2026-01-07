<?php

namespace Tests\Wallabag\Controller;

use Craue\ConfigBundle\Util\Config;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\SiteCredential;

class SiteCredentialControllerTest extends WallabagTestCase
{
    public function testAccessDeniedBecauseFeatureDisabled()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->getContainer()->get(Config::class)->set('restricted_access', 0);

        $client->request('GET', '/site-credentials');

        $this->assertSame(404, $client->getResponse()->getStatusCode());

        $client->getContainer()->get(Config::class)->set('restricted_access', 1);
    }

    public function testListSiteCredential()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/site-credentials');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $body = $crawler->filter('body')->extract(['_text'])[0];

        $this->assertStringContainsString('site_credential.description', $body);
        $this->assertStringContainsString('site_credential.list.create_new_one', $body);
    }

    public function testNewSiteCredential()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/site-credentials/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $body = $crawler->filter('body')->extract(['_text'])[0];

        $this->assertStringContainsString('site_credential.new_site_credential', $body);
        $this->assertStringContainsString('site_credential.form.back_to_list', $body);

        $form = $crawler->filter('button[id=site_credential_save]')->form();

        $data = [
            'site_credential[host]' => 'google.io',
            'site_credential[username]' => 'sergei',
            'site_credential[password]' => 'microsoft',
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertStringContainsString('flashes.site_credential.notice.added', $crawler->filter('body')->extract(['_text'])[0]);
    }

    public function testEditSiteCredential()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $credential = $this->createSiteCredential($client);

        $crawler = $client->request('GET', '/site-credentials/' . $credential->getId() . '/edit');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $body = $crawler->filter('body')->extract(['_text'])[0];

        $this->assertStringContainsString('site_credential.edit_site_credential', $body);
        $this->assertStringContainsString('site_credential.form.back_to_list', $body);

        $form = $crawler->filter('button[id=site_credential_save]')->form();

        $data = [
            'site_credential[host]' => 'google.io',
            'site_credential[username]' => 'larry',
            'site_credential[password]' => 'microsoft',
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertStringContainsString('flashes.site_credential.notice.updated', $crawler->filter('body')->extract(['_text'])[0]);
    }

    public function testEditFromADifferentUserSiteCredential()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $credential = $this->createSiteCredential($client);

        $this->logInAs('bob');

        $client->request('GET', '/site-credentials/' . $credential->getId() . '/edit');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testDeleteSiteCredential()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $credential = $this->createSiteCredential($client);

        $crawler = $client->request('GET', '/site-credentials/' . $credential->getId() . '/edit');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $deleteForm = $crawler->filter('body')->selectButton('site_credential.form.delete')->form();

        $client->submit($deleteForm, []);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertStringContainsString('flashes.site_credential.notice.deleted', $crawler->filter('body')->extract(['_text'])[0]);
    }

    private function createSiteCredential(KernelBrowser $client)
    {
        $credential = new SiteCredential($this->getLoggedInUser());
        $credential->setHost('google.io');
        $credential->setUsername('sergei');
        $credential->setPassword('microsoft');

        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $em->persist($credential);
        $em->flush();

        return $credential;
    }
}
