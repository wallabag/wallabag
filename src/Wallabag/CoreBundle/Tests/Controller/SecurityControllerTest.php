<?php

namespace Wallabag\CoreBundle\Tests\Controller;

use Wallabag\CoreBundle\Tests\WallabagCoreTestCase;

class SecurityControllerTest extends WallabagCoreTestCase
{
    public function testLoginWithout2Factor()
    {
        $this->logInAs('admin');
        $client = $this->getClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/config');
        $this->assertContains('config.form_rss.description', $crawler->filter('body')->extract(array('_text'))[0]);
    }

    public function testLoginWith2Factor()
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
        $user->setTwoFactorAuthentication(true);
        $em->persist($user);
        $em->flush();

        $this->logInAs('admin');
        $crawler = $client->request('GET', '/config');
        $this->assertContains('scheb_two_factor.trusted', $crawler->filter('body')->extract(array('_text'))[0]);

        // restore user
        $user = $em
            ->getRepository('WallabagUserBundle:User')
            ->findOneByUsername('admin');
        $user->setTwoFactorAuthentication(false);
        $em->persist($user);
        $em->flush();
    }

    public function testTrustedComputer()
    {
        $client = $this->getClient();

        if (!$client->getContainer()->getParameter('twofactor_auth')) {
            $this->markTestSkipped('twofactor_auth is not enabled.');

            return;
        }

        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('WallabagUserBundle:User')
            ->findOneByUsername('admin');

        $date = new \DateTime();
        $user->addTrustedComputer('ABCDEF', $date->add(new \DateInterval('P1M')));
        $this->assertTrue($user->isTrustedComputer('ABCDEF'));
        $this->assertFalse($user->isTrustedComputer('FEDCBA'));
    }
}
