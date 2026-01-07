<?php

namespace Tests\Wallabag\Controller\Api;

class TaggingRuleRestControllerTest extends WallabagApiTestCase
{
    public function testExportEntry()
    {
        $this->client->request('GET', '/api/taggingrule/export');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }
}
