<?php

namespace Tests\Wallabag\CoreBundle;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Wallabag\UserBundle\Entity\User;

abstract class WallabagCoreTestCase extends WebTestCase
{
    /**
     * @var KernelBrowser|null
     */
    private $client = null;

    protected function setUp(): void
    {
        static::ensureKernelShutdown();

        parent::setUp();

        $this->client = static::createClient();
    }

    public function getNewClient()
    {
        static::ensureKernelShutdown();

        return $this->client = static::createClient();
    }

    public function getTestClient()
    {
        return $this->client;
    }

    public function resetDatabase(KernelBrowser $client)
    {
        $application = new Application($client->getKernel());
        $application->setAutoExit(false);

        $application->run(new ArrayInput([
            'command' => 'doctrine:schema:drop',
            '--no-interaction' => true,
            '--force' => true,
            '--env' => 'test',
        ]), new NullOutput());

        $application->run(new ArrayInput([
            'command' => 'doctrine:schema:create',
            '--no-interaction' => true,
            '--env' => 'test',
        ]), new NullOutput());

        $application->run(new ArrayInput([
            'command' => 'doctrine:fixtures:load',
            '--no-interaction' => true,
            '--env' => 'test',
        ]), new NullOutput());

        /*
         * Recreate client to avoid error:
         *
         * [Doctrine\DBAL\ConnectionException]
         * Transaction commit failed because the transaction has been marked for rollback only.
         */
        $this->client = static::createClient();
    }

    public function getEntityManager()
    {
        return $this->client->getContainer()->get(EntityManagerInterface::class);
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
        $session = $container->get(SessionInterface::class);

        $userManager = $container->get('fos_user.user_manager.test');
        $loginManager = $container->get('fos_user.security.login_manager.test');
        $firewallName = $container->getParameter('fos_user.firewall_name');

        $user = $userManager->findUserBy(['username' => $username]);

        if (null === $user) {
            throw new \Exception('Unable to find user "' . $username . '". Does fixtures were loaded?');
        }

        $loginManager->logInUser($firewallName, $user);

        $session->set('_security_' . $firewallName, serialize($container->get(TokenStorageInterface::class)->getToken()));
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
     * Return the user of the logged in user.
     * You should be sure that you called `logInAs` before.
     *
     * @return User
     */
    public function getLoggedInUser()
    {
        $token = static::$kernel->getContainer()->get(TokenStorageInterface::class)->getToken();

        if (null !== $token) {
            \assert($token->getUser() instanceof User);

            return $token->getUser();
        }

        throw new \RuntimeException('No logged in User.');
    }

    /**
     * Return the user id of the logged in user.
     * You should be sure that you called `logInAs` before.
     *
     * @return int
     */
    public function getLoggedInUserId()
    {
        return $this->getLoggedInUser()->getId();
    }

    /**
     * Check if Redis is installed.
     * If not, mark test as skip.
     */
    protected function checkRedis()
    {
        try {
            $this->client->getContainer()->get(\Predis\Client::class)->connect();
        } catch (\Exception $e) {
            $this->markTestSkipped('Redis is not installed/activated');
        }
    }
}
