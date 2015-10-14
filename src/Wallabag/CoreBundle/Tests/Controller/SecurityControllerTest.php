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

        $client->request('GET', '/config');
        $this->assertContains('RSS', $client->getResponse()->getContent());
    }

    public function testLoginWith2Factor()
    {
        $client = $this->getClient();
        $client->followRedirects();

        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('WallabagUserBundle:User')
            ->findOneByUsername('admin');
        $user->setTwoFactorAuthentication(true);
        $em->persist($user);
        $em->flush();

        $this->logInAs('admin');
        $client->request('GET', '/config');
        $this->assertContains('trusted computer', $client->getResponse()->getContent());

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
