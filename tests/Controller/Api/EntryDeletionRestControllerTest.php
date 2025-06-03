<?php

namespace Tests\Wallabag\Controller\Api;

/**
 * Test the entry deletion REST API endpoints.
 * 
 * The fixtures set up the following entry deletions:
 * - Admin user: 1 deletion from 4 days ago (entry_id: 1004)
 * - Admin user: 1 deletion from 1 day ago (entry_id: 1001)
 * - Bob user: 1 deletion from 3 days ago (entry_id: 1003)
 * 
 * The logged in user is admin.
 */
class EntryDeletionRestControllerTest extends WallabagApiTestCase
{
    public function testGetEntryDeletions()
    {
        $this->client->request('GET', '/api/entry-deletions');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);
        // check that only the items for the current user are returned
        $this->assertEquals(2, \count($content['_embedded']['items']));

        // validate the deletion schema on the first item
        $deletionData = $content['_embedded']['items'][0];
        $this->assertArrayHasKey('id', $deletionData);
        $this->assertArrayHasKey('entry_id', $deletionData);
        $this->assertArrayHasKey('deleted_at', $deletionData);
        $this->assertArrayNotHasKey('user_id', $deletionData);
    }

    public function testGetEntryDeletionsSince()
    {
        // Test date range filter - get deletions from last 2 days
        $since = (new \DateTime('-2 days'))->getTimestamp();
        $this->client->request('GET', "/api/entry-deletions?since={$since}");
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertGreaterThanOrEqual(1, \count($content['_embedded']['items']));
    }
}
