<?php

namespace Tests\Wallabag\ApiBundle;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

abstract class WallabagApiTestCase extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client = null;

    /**
     * @var \FOS\UserBundle\Model\UserInterface
     */
    protected $user;

    public function setUp()
    {
        $this->client = $this->createAuthorizedClient();
    }

    /**
     * @return Client
     */
    protected function createAuthorizedClient()
    {
        $client = static::createClient();
        $container = $client->getContainer();

        /** @var $userManager \FOS\UserBundle\Doctrine\UserManager */
        $userManager = $container->get('fos_user.user_manager');
        /** @var $loginManager \FOS\UserBundle\Security\LoginManager */
        $loginManager = $container->get('fos_user.security.login_manager');
        $firewallName = $container->getParameter('fos_user.firewall_name');

        $this->user = $userManager->findUserBy(['username' => 'admin']);
        $loginManager->loginUser($firewallName, $this->user);

        // save the login token into the session and put it in a cookie
        $container->get('session')->set('_security_'.$firewallName, serialize($container->get('security.token_storage')->getToken()));
        $container->get('session')->save();

        $session = $container->get('session');
        $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));

        return $client;
    }
}
