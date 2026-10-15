<?php

namespace Wallabag\Tests\Functional\Controller\Api;


use Wallabag\Entity\User;

class GrabberRestControllerTest extends RssApiTestCase
{
    public function testAuthenticatedSettingsAndTenantIsolation(): void
    {
        $this->client->request('GET', '/api/grabbers.json');
        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        self::assertCount(2, json_decode($this->client->getResponse()->getContent(), true));
        $this->client->request('PATCH', '/api/grabbers/medium.json', [], [], ['CONTENT_TYPE' => 'application/json'], '{"enabled":false}');
        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $em = $this->getEntityManager();
        $em->clear();
        self::assertFalse($em->getRepository(User::class)->findOneByUsername('admin')->getConfig()->isMediumGrabberEnabled());
        self::assertTrue($em->getRepository(User::class)->findOneByUsername('bob')->getConfig()->isMediumGrabberEnabled());
        $this->client->request('PATCH', '/api/grabbers/medium.json', [], [], ['CONTENT_TYPE' => 'application/json'], '{"enabled":true,"endpoint":"http://127.0.0.1"}');
        self::assertSame(400, $this->client->getResponse()->getStatusCode());
    }
}
