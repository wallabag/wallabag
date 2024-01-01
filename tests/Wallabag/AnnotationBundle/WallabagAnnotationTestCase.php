<?php

namespace Tests\Wallabag\AnnotationBundle;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
        $firewallName = $container->getParameter('fos_user.firewall_name');

        $this->user = $userManager->findUserBy(['username' => 'admin']);

        $client->loginUser($this->user, $firewallName);

        return $client;
    }
}
