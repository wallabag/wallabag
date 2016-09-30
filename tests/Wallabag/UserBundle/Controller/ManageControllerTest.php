<?php

namespace Wallabag\UserBundle\Tests\Controller;

use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;

class ManageControllerTest extends WallabagCoreTestCase
{
    public function testLogin()
    {
        $client = $this->getClient();

        $client->request('GET', '/users/');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertContains('login', $client->getResponse()->headers->get('location'));
    }

    public function testCompleteScenario()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        // Create a new user in the database
        $crawler = $client->request('GET', '/users/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /users/");
        $crawler = $client->click($crawler->selectLink('user.list.create_new_one')->link());

        // Fill in the form and submit it
        $form = $crawler->selectButton('user.form.save')->form(array(
            'new_user[username]' => 'test_user',
            'new_user[email]' => 'test@test.io',
            'new_user[plainPassword][first]' => 'test',
            'new_user[plainPassword][second]' => 'test',
        ));

        $client->submit($form);
        $client->followRedirect();
        $crawler = $client->request('GET', '/users/');

        // Check data in the show view
        $this->assertGreaterThan(0, $crawler->filter('td:contains("test_user")')->count(), 'Missing element td:contains("test_user")');

        // Edit the user
        $crawler = $client->click($crawler->selectLink('user.list.edit_action')->last()->link());

        $form = $crawler->selectButton('user.form.save')->form(array(
            'user[name]' => 'Foo User',
            'user[username]' => 'test_user',
            'user[email]' => 'test@test.io',
            'user[enabled]' => true,
            'user[locked]' => false,
        ));

        $client->submit($form);
        $crawler = $client->followRedirect();

        // Check the element contains an attribute with value equals "Foo User"
        $this->assertGreaterThan(0, $crawler->filter('[value="Foo User"]')->count(), 'Missing element [value="Foo User"]');

        $crawler = $client->request('GET', '/users/');
        $crawler = $client->click($crawler->selectLink('user.list.edit_action')->last()->link());

        // Delete the user
        $client->submit($crawler->selectButton('user.form.delete')->form());
        $crawler = $client->followRedirect();

        // Check the user has been delete on the list
        $this->assertNotRegExp('/Foo User/', $client->getResponse()->getContent());
    }
}
