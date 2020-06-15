<?php

namespace Tests\Wallabag\CoreBundle\Controller;

use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;

class SecurityControllerTest extends WallabagCoreTestCase
{
    public function testLoginWithEmail()
    {
        $this->logInAsUsingHttp('bigboss@wallabag.org');
        $client = $this->getClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/config');
        $this->assertStringContainsString('config.form_feed.description', $crawler->filter('body')->extract(['_text'])[0]);
    }

    public function testLoginWithout2Factor()
    {
        $this->logInAs('admin');
        $client = $this->getClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/config');
        $this->assertStringContainsString('config.form_feed.description', $crawler->filter('body')->extract(['_text'])[0]);
    }

    public function testLoginWith2FactorEmail()
    {
        $client = $this->getClient();

        if (!$client->getContainer()->getParameter('twofactor_auth')) {
            $this->markTestSkipped('twofactor_auth is not enabled.');

            return;
        }

        $client->followRedirects();

        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('WallabagUserBundle:User')
            ->findOneByUsername('admin');
        $user->setEmailTwoFactor(true);
        $em->persist($user);
        $em->flush();

        $this->logInAsUsingHttp('admin');
        $crawler = $client->request('GET', '/config');
        $this->assertStringContainsString('trusted', $crawler->filter('body')->extract(['_text'])[0]);

        // restore user
        $user = $em
            ->getRepository('WallabagUserBundle:User')
            ->findOneByUsername('admin');
        $user->setEmailTwoFactor(false);
        $em->persist($user);
        $em->flush();
    }

    public function testLoginWith2FactorGoogle()
    {
        $client = $this->getClient();

        if (!$client->getContainer()->getParameter('twofactor_auth')) {
            $this->markTestSkipped('twofactor_auth is not enabled.');

            return;
        }

        $client->followRedirects();

        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('WallabagUserBundle:User')
            ->findOneByUsername('admin');
        $user->setGoogleAuthenticatorSecret('26LDIHYGHNELOQEM');
        $em->persist($user);
        $em->flush();

        $this->logInAsUsingHttp('admin');
        $crawler = $client->request('GET', '/config');
        $this->assertStringContainsString('trusted', $crawler->filter('body')->extract(['_text'])[0]);

        // restore user
        $user = $em
            ->getRepository('WallabagUserBundle:User')
            ->findOneByUsername('admin');
        $user->setGoogleAuthenticatorSecret(null);
        $em->persist($user);
        $em->flush();
    }

    public function testEnabledRegistration()
    {
        $client = $this->getClient();

        if (!$client->getContainer()->getParameter('fosuser_registration')) {
            $this->markTestSkipped('fosuser_registration is not enabled.');

            return;
        }

        $client->followRedirects();
        $client->request('GET', '/register');
        $this->assertStringContainsString('registration.submit', $client->getResponse()->getContent());
    }
}
