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

    public function testGetUserWithoutAuthentication()
    {
        $client = static::createClient();
        $client->request('GET', '/api/user.json');
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $content);
        $this->assertArrayHasKey('error_description', $content);

        $this->assertEquals('access_denied', $content['error']);

        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));
    }

    public function testCreateNewUser()
    {
        $this->client->getContainer()->get('craue_config')->set('api_user_registration', 1);
        $this->client->request('PUT', '/api/user.json', [
            'username' => 'google',
            'password' => 'googlegoogle',
            'email' => 'wallabag@google.com',
        ]);

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('email', $content);
        $this->assertArrayHasKey('username', $content);
        $this->assertArrayHasKey('created_at', $content);
        $this->assertArrayHasKey('updated_at', $content);
        $this->assertArrayHasKey('default_client', $content);

        $this->assertEquals('wallabag@google.com', $content['email']);
        $this->assertEquals('google', $content['username']);

        $this->assertArrayHasKey('client_secret', $content['default_client']);
        $this->assertArrayHasKey('client_id', $content['default_client']);

        $this->assertEquals('Default client', $content['default_client']['name']);

        $this->assertEquals('application/json', $this->client->getResponse()->headers->get('Content-Type'));

        $this->client->getContainer()->get('craue_config')->set('api_user_registration', 0);
    }

    public function testCreateNewUserWithoutAuthentication()
    {
        // create a new client instead of using $this->client to be sure client isn't authenticated
        $client = static::createClient();
        $client->getContainer()->get('craue_config')->set('api_user_registration', 1);
        $client->request('PUT', '/api/user.json', [
            'username' => 'google',
            'password' => 'googlegoogle',
            'email' => 'wallabag@google.com',
            'client_name' => 'My client name !!',
        ]);

        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('email', $content);
        $this->assertArrayHasKey('username', $content);
        $this->assertArrayHasKey('created_at', $content);
        $this->assertArrayHasKey('updated_at', $content);
        $this->assertArrayHasKey('default_client', $content);

        $this->assertEquals('wallabag@google.com', $content['email']);
        $this->assertEquals('google', $content['username']);

        $this->assertArrayHasKey('client_secret', $content['default_client']);
        $this->assertArrayHasKey('client_id', $content['default_client']);

        $this->assertEquals('My client name !!', $content['default_client']['name']);

        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));

        $client->getContainer()->get('craue_config')->set('api_user_registration', 0);
    }

    public function testCreateNewUserWithExistingEmail()
    {
        $client = static::createClient();
        $client->getContainer()->get('craue_config')->set('api_user_registration', 1);
        $client->request('PUT', '/api/user.json', [
            'username' => 'admin',
            'password' => 'googlegoogle',
            'email' => 'bigboss@wallabag.org',
        ]);

        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $content);
        $this->assertArrayHasKey('username', $content['error']);
        $this->assertArrayHasKey('email', $content['error']);

        // $this->assertEquals('fos_user.username.already_used', $content['error']['username'][0]);
        // $this->assertEquals('fos_user.email.already_used', $content['error']['email'][0]);
        // This shouldn't be translated ...
        $this->assertEquals('This value is already used.', $content['error']['username'][0]);
        $this->assertEquals('This value is already used.', $content['error']['email'][0]);

        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));

        $client->getContainer()->get('craue_config')->set('api_user_registration', 0);
    }

    public function testCreateNewUserWithTooShortPassword()
    {
        $client = static::createClient();
        $client->getContainer()->get('craue_config')->set('api_user_registration', 1);
        $client->request('PUT', '/api/user.json', [
            'username' => 'facebook',
            'password' => 'face',
            'email' => 'facebook@wallabag.org',
        ]);

        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $content);
        $this->assertArrayHasKey('password', $content['error']);

        $this->assertEquals('validator.password_too_short', $content['error']['password'][0]);

        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));

        $client->getContainer()->get('craue_config')->set('api_user_registration', 0);
    }

    public function testCreateNewUserWhenRegistrationIsDisabled()
    {
        $client = static::createClient();
        $client->request('PUT', '/api/user.json', [
            'username' => 'facebook',
            'password' => 'face',
            'email' => 'facebook@wallabag.org',
        ]);

        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $content);

        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));
    }
}
