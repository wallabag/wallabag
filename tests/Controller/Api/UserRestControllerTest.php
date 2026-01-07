<?php

namespace Tests\Wallabag\Controller\Api;

use Craue\ConfigBundle\Util\Config;

class UserRestControllerTest extends WallabagApiTestCase
{
    public function testGetUser()
    {
        $this->client->request('GET', '/api/user.json');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('email', $content);
        $this->assertArrayHasKey('name', $content);
        $this->assertArrayHasKey('username', $content);
        $this->assertArrayHasKey('created_at', $content);
        $this->assertArrayHasKey('updated_at', $content);

        $this->assertSame('bigboss@wallabag.org', $content['email']);
        $this->assertSame('Big boss', $content['name']);
        $this->assertSame('admin', $content['username']);

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetUserWithoutAuthentication()
    {
        $client = $this->createUnauthorizedClient();
        $client->request('GET', '/api/user.json');
        $this->assertSame(401, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $content);
        $this->assertArrayHasKey('error_description', $content);

        $this->assertSame('access_denied', $content['error']);

        $this->assertSame('application/json', $client->getResponse()->headers->get('Content-Type'));
    }

    public function testCreateNewUser()
    {
        $this->client->getContainer()->get(Config::class)->set('api_user_registration', '1');
        $this->client->request('PUT', '/api/user.json', [
            'username' => 'google',
            'password' => 'googlegoogle',
            'email' => 'wallabag@google.com',
        ]);

        $this->assertSame(201, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('email', $content);
        $this->assertArrayHasKey('username', $content);
        $this->assertArrayHasKey('created_at', $content);
        $this->assertArrayHasKey('updated_at', $content);
        $this->assertArrayHasKey('default_client', $content);

        $this->assertSame('wallabag@google.com', $content['email']);
        $this->assertSame('google', $content['username']);

        $this->assertArrayHasKey('client_secret', $content['default_client']);
        $this->assertArrayHasKey('client_id', $content['default_client']);

        $this->assertSame('Default client', $content['default_client']['name']);

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));

        $this->client->getContainer()->get(Config::class)->set('api_user_registration', '0');
    }

    public function testCreateNewUserWithoutAuthentication()
    {
        // create a new client instead of using $this->client to be sure client isn't authenticated
        $client = $this->createUnauthorizedClient();
        $client->getContainer()->get(Config::class)->set('api_user_registration', '1');
        $client->request('PUT', '/api/user.json', [
            'username' => 'google',
            'password' => 'googlegoogle',
            'email' => 'wallabag@google.com',
            'client_name' => 'My client name !!',
        ]);

        $this->assertSame(201, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('email', $content);
        $this->assertArrayHasKey('username', $content);
        $this->assertArrayHasKey('created_at', $content);
        $this->assertArrayHasKey('updated_at', $content);
        $this->assertArrayHasKey('default_client', $content);

        $this->assertSame('wallabag@google.com', $content['email']);
        $this->assertSame('google', $content['username']);

        $this->assertArrayHasKey('client_secret', $content['default_client']);
        $this->assertArrayHasKey('client_id', $content['default_client']);

        $this->assertSame('My client name !!', $content['default_client']['name']);

        $this->assertSame('application/json', $client->getResponse()->headers->get('Content-Type'));

        $client->getContainer()->get(Config::class)->set('api_user_registration', '0');
    }

    public function testCreateNewUserWithExistingEmail()
    {
        $client = $this->createUnauthorizedClient();
        $client->getContainer()->get(Config::class)->set('api_user_registration', '1');
        $client->request('PUT', '/api/user.json', [
            'username' => 'admin',
            'password' => 'googlegoogle',
            'email' => 'bigboss@wallabag.org',
        ]);

        $this->assertSame(400, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $content);
        $this->assertArrayHasKey('username', $content['error']);
        $this->assertArrayHasKey('email', $content['error']);

        // $this->assertEquals('fos_user.username.already_used', $content['error']['username'][0]);
        // $this->assertEquals('fos_user.email.already_used', $content['error']['email'][0]);
        // This shouldn't be translated ...
        $this->assertSame('This value is already used.', $content['error']['username'][0]);
        $this->assertSame('This value is already used.', $content['error']['email'][0]);

        $this->assertSame('application/json', $client->getResponse()->headers->get('Content-Type'));

        $client->getContainer()->get(Config::class)->set('api_user_registration', '0');
    }

    public function testCreateNewUserWithTooShortPassword()
    {
        $client = $this->createUnauthorizedClient();
        $client->getContainer()->get(Config::class)->set('api_user_registration', '1');
        $client->request('PUT', '/api/user.json', [
            'username' => 'facebook',
            'password' => 'face',
            'email' => 'facebook@wallabag.org',
        ]);

        $this->assertSame(400, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $content);
        $this->assertArrayHasKey('password', $content['error']);

        $this->assertSame('validator.password_too_short', $content['error']['password'][0]);

        $this->assertSame('application/json', $client->getResponse()->headers->get('Content-Type'));

        $client->getContainer()->get(Config::class)->set('api_user_registration', '0');
    }

    public function testCreateNewUserWhenRegistrationIsDisabled()
    {
        $client = $this->createUnauthorizedClient();
        $client->request('PUT', '/api/user.json', [
            'username' => 'facebook',
            'password' => 'face',
            'email' => 'facebook@wallabag.org',
        ]);

        $this->assertSame(403, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $content);

        $this->assertSame('application/json', $client->getResponse()->headers->get('Content-Type'));
    }
}
