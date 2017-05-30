<?php

namespace Tests\Wallabag\ApiBundle\Controller;

use Tests\Wallabag\ApiBundle\WallabagApiTestCase;

class UserRestControllerTest extends WallabagApiTestCase
{
    public function testGetUser()
    {
        $this->client->request('GET', '/api/user.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('email', $content);
        $this->assertArrayHasKey('name', $content);
        $this->assertArrayHasKey('username', $content);
        $this->assertArrayHasKey('created_at', $content);
        $this->assertArrayHasKey('updated_at', $content);

        $this->assertEquals('bigboss@wallabag.org', $content['email']);
        $this->assertEquals('Big boss', $content['name']);
        $this->assertEquals('admin', $content['username']);

        $this->assertEquals('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testCreateNewUser()
    {
        $this->client->request('PUT', '/api/user.json', [
            'username' => 'google',
            'password' => 'googlegoogle',
            'email' => 'wallabag@google.com',
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('email', $content);
        $this->assertArrayHasKey('username', $content);
        $this->assertArrayHasKey('created_at', $content);
        $this->assertArrayHasKey('updated_at', $content);

        $this->assertEquals('wallabag@google.com', $content['email']);
        $this->assertEquals('google', $content['username']);

        $this->assertEquals('application/json', $this->client->getResponse()->headers->get('Content-Type'));

        // remove the created user to avoid side effect on other tests
        // @todo remove these lines when test will be isolated
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $query = $em->createQuery('DELETE FROM Wallabag\CoreBundle\Entity\Config c WHERE c.user = :user_id');
        $query->setParameter('user_id', $content['id']);
        $query->execute();

        $query = $em->createQuery('DELETE FROM Wallabag\UserBundle\Entity\User u WHERE u.id = :id');
        $query->setParameter('id', $content['id']);
        $query->execute();
    }

    public function testCreateNewUserWithExistingEmail()
    {
        $this->client->request('PUT', '/api/user.json', [
            'username' => 'admin',
            'password' => 'googlegoogle',
            'email' => 'bigboss@wallabag.org',
        ]);

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $content);
        $this->assertArrayHasKey('username', $content['error']);
        $this->assertArrayHasKey('email', $content['error']);

        // $this->assertEquals('fos_user.username.already_used', $content['error']['username'][0]);
        // $this->assertEquals('fos_user.email.already_used', $content['error']['email'][0]);
        // This shouldn't be translated ...
        $this->assertEquals('This value is already used.', $content['error']['username'][0]);
        $this->assertEquals('This value is already used.', $content['error']['email'][0]);

        $this->assertEquals('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testCreateNewUserWithTooShortPassword()
    {
        $this->client->request('PUT', '/api/user.json', [
            'username' => 'facebook',
            'password' => 'face',
            'email' => 'facebook@wallabag.org',
        ]);

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $content);
        $this->assertArrayHasKey('password', $content['error']);

        $this->assertEquals('validator.password_too_short', $content['error']['password'][0]);

        $this->assertEquals('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }
}
