<?php

namespace Tests\Wallabag\CoreBundle;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
}
