<?php

namespace Wallabag\Tests\Functional\Controller;

use Wallabag\Tests\Functional\WallabagTestCase;

/**
 * The controller `SettingsController` does not exist.
 * This test cover security against the internal settings page managed by CraueConfigBundle.
 */
class SettingsControllerTest extends WallabagTestCase
{
    public function testSettingsWithAdmin(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/settings');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testSettingsWithNormalUser(): void
    {
        $this->logInAs('bob');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/settings');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }
}
