<?php

namespace Tests\Wallabag\AnnotationBundle;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

abstract class WallabagAnnotationTestCase extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client = null;

    /**
     * @var \FOS\UserBundle\Model\UserInterface
     */
    protected $user;

    protected function setUp(): void
    {
        $this->client = $this->createAuthorizedClient();
    }

    public function logInAs($username)
    {
        $container = $this->client->getContainer();
        $session = $container->get('session');

        $userManager = $container->get('fos_user.user_manager.test');
        $loginManager = $container->get('fos_user.security.login_manager.test');
        $firewallName = $container->getParameter('fos_user.firewall_name');

        $user = $userManager->findUserBy(['username' => $username]);

        if (null === $user) {
            throw new \Exception('Unable to find user "' . $username . '". Does fixtures were loaded?');
        }

        $loginManager->logInUser($firewallName, $user);

        $session->set('_security_' . $firewallName, serialize($container->get('security.token_storage')->getToken()));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
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
}
