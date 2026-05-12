<?php

namespace Wallabag\Tests\Functional\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Wallabag\Entity\User;
use Wallabag\Tests\Functional\WallabagTestCase;

class UserControllerTest extends WallabagTestCase
{
    public function testLogin(): void
    {
        $client = $this->getTestClient();

        $client->request('GET', '/users/list');

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('login', $client->getResponse()->headers->get('location'));
    }

    public function testCompleteScenario(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $username = 'test_user_' . uniqid('', true);
        $email = $username . '@test.io';

        // Create a new user in the database
        $crawler = $client->request('GET', '/users/list');
        $this->assertSame(200, $client->getResponse()->getStatusCode(), 'Unexpected HTTP status code for GET /users/');
        $crawler = $client->click($crawler->selectLink('user.list.create_new_one')->link());

        // Fill in the form and submit it
        $form = $crawler->selectButton('user.form.save')->form([
            'new_user[username]' => $username,
            'new_user[email]' => $email,
            'new_user[plainPassword][first]' => 'testtest',
            'new_user[plainPassword][second]' => 'testtest',
        ]);

        $client->submit($form);
        $client->followRedirect();
        $crawler = $client->request('GET', '/users/list');
        $user = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(User::class)
            ->findOneBy(['username' => $username]);

        \assert($user instanceof User);

        // Check data in the show view
        $this->assertGreaterThan(0, $crawler->filter('td:contains("' . $username . '")')->count(), 'Missing element td:contains("' . $username . '")');

        // Edit the user
        $crawler = $client->request('GET', '/users/' . $user->getId() . '/edit');

        $form = $crawler->selectButton('user.form.save')->form([
            'user[name]' => 'Foo User',
            'user[username]' => $username,
            'user[email]' => $email,
            'user[enabled]' => true,
        ]);

        $client->submit($form);
        $crawler = $client->followRedirect();

        // Check the element contains an attribute with value equals "Foo User"
        $this->assertGreaterThan(0, $crawler->filter('[value="Foo User"]')->count(), 'Missing element [value="Foo User"]');

        $crawler = $client->request('GET', '/users/' . $user->getId() . '/edit');

        // Delete the user
        $client->submit($crawler->selectButton('user.form.delete')->form());
        $crawler = $client->followRedirect();

        // Check the user has been delete on the list
        $this->assertDoesNotMatchRegularExpression('/Foo User/', $client->getResponse()->getContent());
    }

    public function testDeleteDisabledForLoggedUser(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/users/' . $this->getLoggedInUserId() . '/edit');
        $disabled = $crawler->selectButton('user.form.delete')->extract(['disabled']);

        $this->assertSame('disabled', $disabled[0]);
    }

    public function testUserSearch(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        // Search on unread list
        $crawler = $client->request('GET', '/users/list');

        $form = $crawler->filter('form[name=search_users]')->form();
        $data = [
            'search_user[term]' => 'admin',
        ];

        $crawler = $client->submit($form, $data);

        $this->assertCount(2, $crawler->filter('tr')); // 1 result + table header
    }
}
