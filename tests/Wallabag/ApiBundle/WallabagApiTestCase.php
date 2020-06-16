<?php

namespace Tests\Wallabag\ApiBundle;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

abstract class WallabagApiTestCase extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client = null;

    /**
     * @var \FOS\UserBundle\Model\UserInterface
     */
    protected $user;

    public function setUp(): void
    {
        $this->client = $this->createAuthorizedClient();
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected function createAuthorizedClient()
    {
        $client = static::createClient();
        $container = $client->getContainer();

        /** @var $userManager \FOS\UserBundle\Doctrine\UserManager */
        $userManager = $container->get('fos_user.user_manager.test');
        /** @var $loginManager \FOS\UserBundle\Security\LoginManager */
        $loginManager = $container->get('fos_user.security.login_manager.test');
        $firewallName = $container->getParameter('fos_user.firewall_name');

        $this->user = $userManager->findUserBy(['username' => 'admin']);
        $loginManager->logInUser($firewallName, $this->user);

        // save the login token into the session and put it in a cookie
        $container->get('session')->set('_security_' . $firewallName, serialize($container->get('security.token_storage')->getToken()));
        $container->get('session')->save();

        $session = $container->get('session');
        $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));

        return $client;
    }

    /**
     * Return the ID for the user admin.
     * Used because on heavy testing we don't want to re-create the database on each run.
     * Which means "admin" user won't have id 1 all the time.
     *
     * @param string $username
     *
     * @return int
     */
    protected function getUserId($username = 'admin')
    {
        return $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagUserBundle:User')
            ->findOneByUserName($username)
            ->getId();
    }
}
