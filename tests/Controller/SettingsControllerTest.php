<?php

namespace Tests\Wallabag\Controller;

use Tests\Wallabag\WallabagTestCase;

/**
 * The controller `SettingsController` does not exist.
 * This test cover security against the internal settings page managed by CraueConfigBundle.
 */
class SettingsControllerTest extends WallabagTestCase
{
    public function testSettingsWithAdmin()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/settings');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testSettingsWithNormalUser()
    {
        $this->logInAs('bob');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/settings');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }
}
