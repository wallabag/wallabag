<?php

namespace Wallabag\CoreBundle\Tests\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Wallabag\CoreBundle\Tests\WallabagCoreTestCase;

class SecurityControllerTest extends WallabagCoreTestCase
{
    public function testLogin()
    {
        $client = $this->getClient();

        $crawler = $client->request('GET', '/new');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertContains('login', $client->getResponse()->headers->get('location'));
    }

    public function testLoginFail()
    {
        $client = $this->getClient();

        $crawler = $client->request('GET', '/login');

        $form = $crawler->filter('button[type=submit]')->form();
        $data = array(
            '_username' => 'admin',
            '_password' => 'admin',
        );

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertContains('login', $client->getResponse()->headers->get('location'));

        $crawler = $client->followRedirect();

        $this->assertContains('Bad credentials', $client->getResponse()->getContent());
    }

    public function testForgotPassword()
    {
        $client = $this->getClient();

        $crawler = $client->request('GET', '/forgot-password');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertContains('Forgot password', $client->getResponse()->getContent());

        $form = $crawler->filter('button[type=submit]');

        $this->assertCount(1, $form);

        return array(
            'form' => $form->form(),
            'client' => $client,
        );
    }

    /**
     * @depends testForgotPassword
     */
    public function testSubmitForgotPasswordFail($parameters)
    {
        $form = $parameters['form'];
        $client = $parameters['client'];

        $data = array(
            'forgot_password[email]' => 'material',
        );

        $client->submit($form, $data);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('No user found with this email', $client->getResponse()->getContent());
    }

    /**
     * @depends testForgotPassword
     *
     * Instead of using collector which slow down the test suite
     * http://symfony.com/doc/current/cookbook/email/testing.html
     *
     * Use a different way where Swift store email as file
     */
    public function testSubmitForgotPassword($parameters)
    {
        $form = $parameters['form'];
        $client = $parameters['client'];

        $spoolDir = $client->getKernel()->getContainer()->getParameter('swiftmailer.spool.default.file.path');

        // cleanup pool dir
        $filesystem = new Filesystem();
        $filesystem->remove($spoolDir);

        // to use `getCollector` since `collect: false` in config_test.yml
        $client->enableProfiler();

        $data = array(
            'forgot_password[email]' => 'bobby@wallabag.org',
        );

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertContains('An email has been sent to', $client->getResponse()->getContent());

        // find every files (ie: emails) inside the spool dir except hidden files
        $finder = new Finder();
        $finder
            ->in($spoolDir)
            ->ignoreDotFiles(true)
            ->files();

        $this->assertCount(1, $finder, 'Only one email has been sent');

        foreach ($finder as $file) {
            $message = unserialize(file_get_contents($file));

            $this->assertInstanceOf('Swift_Message', $message);
            $this->assertEquals('Reset Password', $message->getSubject());
            $this->assertEquals('no-reply@wallabag.org', key($message->getFrom()));
            $this->assertEquals('bobby@wallabag.org', key($message->getTo()));
            $this->assertContains(
                'To reset your password - please visit',
                $message->getBody()
            );
        }
    }

    public function testReset()
    {
        $client = $this->getClient();
        $user = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:User')
            ->findOneByEmail('bobby@wallabag.org');

        $crawler = $client->request('GET', '/forgot-password/'.$user->getConfirmationToken());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(2, $crawler->filter('input[type=password]'));
        $this->assertCount(1, $form = $crawler->filter('button[type=submit]'));
        $this->assertCount(1, $form);

        $data = array(
            'change_passwd[new_password][first]' => 'mypassword',
            'change_passwd[new_password][second]' => 'mypassword',
        );

        $client->submit($form->form(), $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertContains('login', $client->getResponse()->headers->get('location'));
    }

    public function testResetBadToken()
    {
        $client = $this->getClient();

        $client->request('GET', '/forgot-password/UIZOAU29UE902IEPZO');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testCheckEmailWithoutEmail()
    {
        $client = $this->getClient();

        $client->request('GET', '/forgot-password/check-email');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertContains('forgot-password', $client->getResponse()->headers->get('location'));
    }
}
