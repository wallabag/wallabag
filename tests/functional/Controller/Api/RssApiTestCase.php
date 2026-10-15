<?php

namespace Wallabag\Tests\Functional\Controller\Api;

use Wallabag\Tests\Functional\WallabagTestCase;
use Wallabag\Entity\Api\Client;
use Wallabag\Entity\User;

/** Real fixture OAuth authentication, including across kernel/browser reboots. */
abstract class RssApiTestCase extends WallabagTestCase
{
    protected $client;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->getTestClient();
        $em = $this->getEntityManager();
        $this->user = $em->getRepository(User::class)->findOneByUsername('admin');
        $api = new Client($this->user);
        $api->setName('Local RSS fixture');
        $api->setAllowedGrantTypes(['password']);
        $em->persist($api);
        $em->flush();
        $this->client->request('POST', '/oauth/v2/token', ['grant_type' => 'password', 'client_id' => $api->getPublicId(), 'client_secret' => $api->getSecret(), 'username' => 'admin', 'password' => 'mypassword']);
        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $token = json_decode($this->client->getResponse()->getContent(), true)['access_token'];
        $this->client->restart();
        $this->client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer ' . $token);
    }
}
