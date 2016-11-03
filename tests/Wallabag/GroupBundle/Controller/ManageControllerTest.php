<?php

namespace Wallabag\GroupBundle\Tests\Controller;

use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;

class ManageControllerTest extends WallabagCoreTestCase
{
    public function testLogin()
    {
        $client = $this->getClient();

        $client->request('GET', '/groups/');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertContains('login', $client->getResponse()->headers->get('location'));
    }

    public function testCompleteScenario()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        // Create a new group in the database
        $crawler = $client->request('GET', '/groups/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), 'Unexpected HTTP status code for GET /groups/');
        $crawler = $client->click($crawler->selectLink('group.list.create_new_one')->link());

        // Fill in the form and submit it
        $form = $crawler->selectButton('group.form.save')->form(array(
            'new_group[name]' => 'test_group',
        ));

        $client->submit($form);
        $client->followRedirect();
        $crawler = $client->request('GET', '/groups/');

        // Check data in the show view
        $this->assertGreaterThan(0, $crawler->filter('td:contains("test_group")')->count(), 'Missing element td:contains("test_group")');

        // Edit the group
        $crawler = $client->click($crawler->selectLink('group.list.edit_action')->last()->link());

        $form = $crawler->selectButton('group.form.save')->form(array(
            'group[name]' => 'test_group',
        ));

        $client->submit($form);
        $crawler = $client->followRedirect();

        // Check the element contains an attribute with value equals "test_group"
        $this->assertGreaterThan(0, $crawler->filter('[value="test_group"]')->count(), 'Missing element [value="test_group"]');

        $crawler = $client->request('GET', '/groups/');
        $crawler = $client->click($crawler->selectLink('group.list.edit_action')->last()->link());

        // Delete the group
        $client->submit($crawler->selectButton('group.form.delete')->form());
        $crawler = $client->followRedirect();

        // Check the user has been delete on the list
        $this->assertNotRegExp('/test_group/', $client->getResponse()->getContent());
    }
}
