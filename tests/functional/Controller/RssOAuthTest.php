<?php

namespace Wallabag\Tests\Functional\Controller;

use Wallabag\Entity\Api\Client;
use Wallabag\Entity\RssFeed;
use Wallabag\Entity\User;
use Wallabag\Tests\Functional\WallabagTestCase;

/** Uses only synthetic OAuth clients/tokens in the isolated fixture database. */
class RssOAuthTest extends WallabagTestCase
{
    public function testBearerTokenCanManageOnlyItsOwnersSubscriptions(): void
    {
        $client = $this->getTestClient();
        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->findOneByUsername('admin');
        $apiClient = new Client($user);
        $apiClient->setName('RSS test fixture');
        $apiClient->setAllowedGrantTypes(['password']);
        $em->persist($apiClient);
        $other = $em->getRepository(User::class)->findOneByUsername('bob');
        $foreignFeed = new RssFeed($other, 'https://1.1.1.1/foreign');
        $em->persist($foreignFeed);
        $em->flush();
        $foreignId = $foreignFeed->id;
        $client->request('POST', '/oauth/v2/token', ['grant_type' => 'password', 'client_id' => $apiClient->getPublicId(), 'client_secret' => $apiClient->getSecret(), 'username' => 'admin', 'password' => 'mypassword']);
        self::assertSame(200, $client->getResponse()->getStatusCode());
        $token = json_decode($client->getResponse()->getContent(), true)['access_token'];
        $client->restart();
        $headers = ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'];
        $client->request('POST', '/api/rss/feeds.json', [], [], $headers, '{"url":"https://1.1.1.1/oauth","name":"OAuth test"}');
        self::assertSame(201, $client->getResponse()->getStatusCode());
        $id = json_decode($client->getResponse()->getContent(), true)['id'];
        foreach (['GET', 'PATCH', 'DELETE'] as $method) {
            $client->request($method, '/api/rss/feeds/' . $foreignId . '.json', [], [], $headers, '{"enabled":false}');
            self::assertSame(404, $client->getResponse()->getStatusCode());
        }
        $client->request('GET', '/api/rss/feeds.json', [], [], $headers);
        self::assertSame(200, $client->getResponse()->getStatusCode());
        $ids = array_column(json_decode($client->getResponse()->getContent(), true), 'id');
        self::assertContains($id, $ids);
        self::assertNotContains($foreignId, $ids);
        $client->request('GET', '/api/rss/feeds.json', [], [], ['HTTP_AUTHORIZATION' => 'Bearer invalid']);
        self::assertContains($client->getResponse()->getStatusCode(), [401, 403]);
    }
}
