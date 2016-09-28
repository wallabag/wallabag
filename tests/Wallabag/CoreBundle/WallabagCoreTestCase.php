<?php

namespace Tests\Wallabag\CoreBundle;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

abstract class WallabagCoreTestCase extends WebTestCase
{
    private $client = null;

    public function getClient()
    {
        return $this->client;
    }

    public function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    /**
     * Login a user without making a HTTP request.
     * If we make a HTTP request we lose ability to mock service in the container.
     *
     * @param string $username User to log in
     */
    public function logInAs($username)
    {
        $container = $this->client->getContainer();
        $session = $container->get('session');

        $userManager = $container->get('fos_user.user_manager');
        $loginManager = $container->get('fos_user.security.login_manager');
        $firewallName = $container->getParameter('fos_user.firewall_name');

        $user = $userManager->findUserBy(array('username' => $username));
        $loginManager->loginUser($firewallName, $user);

        $session->set('_security_'.$firewallName, serialize($container->get('security.token_storage')->getToken()));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    /**
     * Instead of `logInAs` this method use a HTTP request to log in the user.
     * Could be better for some tests.
     *
     * @param string $username User to log in
     */
    public function logInAsUsingHttp($username)
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
     * Return the user id of the logged in user.
     * You should be sure that you called `logInAs` before.
     *
     * @return int
     */
    public function getLoggedInUserId()
    {
        $token = static::$kernel->getContainer()->get('security.token_storage')->getToken();

        if (null !== $token) {
            return $token->getUser()->getId();
        }

        throw new \RuntimeException('No logged in User.');
    }

    /**
     * Check if Redis is installed.
     * If not, mark test as skip.
     */
    protected function checkRedis()
    {
        try {
            $this->client->getContainer()->get('wallabag_core.redis.client')->connect();
        } catch (\Exception $e) {
            $this->markTestSkipped(
              'Redis is not installed/activated'
            );
        }
    }
}
