<?php

namespace Tests\Wallabag\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\User;

class SecurityControllerTest extends WallabagTestCase
{
    public function testLoginWithEmail()
    {
        $this->logInAsUsingHttp('bigboss@wallabag.org');
        $client = $this->getTestClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/config');
        $this->assertStringContainsString('config.form_feed.description', $crawler->filter('body')->extract(['_text'])[0]);
    }

    public function testLoginWithout2Factor()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/config');
        $this->assertStringContainsString('config.form_feed.description', $crawler->filter('body')->extract(['_text'])[0]);
    }

    public function testLoginWith2FactorEmail()
    {
        $client = $this->getTestClient();

        $client->followRedirects();

        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');
        $user->setEmailTwoFactor(true);
        $em->persist($user);
        $em->flush();

        $this->logInAsUsingHttp('admin');
        $crawler = $client->request('GET', '/config');
        $this->assertStringContainsString('trusted', $crawler->filter('body')->extract(['_text'])[0]);

        // restore user
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');
        $user->setEmailTwoFactor(false);
        $em->persist($user);
        $em->flush();
    }

    public function testLoginWith2FactorGoogle()
    {
        $client = $this->getTestClient();

        $client->followRedirects();

        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');
        $user->setGoogleAuthenticatorSecret('26LDIHYGHNELOQEM');
        $em->persist($user);
        $em->flush();

        $this->logInAsUsingHttp('admin');
        $crawler = $client->request('GET', '/config');
        $this->assertStringContainsString('trusted', $crawler->filter('body')->extract(['_text'])[0]);

        // restore user
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');
        $user->setGoogleAuthenticatorSecret(null);
        $em->persist($user);
        $em->flush();
    }

    public function testEnabledRegistration()
    {
        $client = $this->getTestClient();

        if (!$client->getContainer()->getParameter('fosuser_registration')) {
            $this->markTestSkipped('fosuser_registration is not enabled.');
        }

        $client->followRedirects();
        $client->request('GET', '/register');
        $this->assertStringContainsString('registration.submit', $client->getResponse()->getContent());
    }
}
