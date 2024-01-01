<?php

namespace Tests\Wallabag\AnnotationBundle;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManager;
use FOS\UserBundle\Security\LoginManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class WallabagAnnotationTestCase extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    protected $client;

    /**
     * @var UserInterface
     */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createAuthorizedClient();
    }

    public function logInAs($username)
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->filter('button[type=submit]')->form();
        $data = [
            '_username' => $username,
            '_password' => 'mypassword',
        ];

        $this->client->submit($form, $data);
    }

    /**
     * @return KernelBrowser
     */
    protected function createAuthorizedClient()
    {
        $client = static::createClient();
        $container = $client->getContainer();

        /** @var UserManager $userManager */
        $userManager = $container->get('fos_user.user_manager.test');
        /** @var LoginManager $loginManager */
        $loginManager = $container->get('fos_user.security.login_manager.test');
        $firewallName = $container->getParameter('fos_user.firewall_name');

        $this->user = $userManager->findUserBy(['username' => 'admin']);
        $loginManager->logInUser($firewallName, $this->user);

        // save the login token into the session and put it in a cookie
        $container->get(SessionInterface::class)->set('_security_' . $firewallName, serialize($container->get(TokenStorageInterface::class)->getToken()));
        $container->get(SessionInterface::class)->save();

        $session = $container->get(SessionInterface::class);
        $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));

        return $client;
    }
}
