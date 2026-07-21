<?php

namespace Wallabag\Tests\Functional\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Wallabag\Entity\User;
use Wallabag\Form\Type\RegistrationFormType;
use Wallabag\Tests\Functional\WallabagTestCase;

class CaptchaRegistrationControllerTest extends WallabagTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getTestClient()->disableReboot();
        $this->getTestClient()->getContainer()->set(RegistrationFormType::class, new RegistrationFormType(true));
    }

    public function testRegistrationAcceptsCaptcha(): void
    {
        $client = $this->getTestClient();
        $crawler = $client->request('GET', '/register/');

        $captchaInput = $crawler->filter('input[name="fos_user_registration[captcha]"]');
        $this->assertCount(1, $captchaInput);
        $this->assertCount(0, $crawler->filter('label[for="fos_user_registration_captcha"]'));
        $this->assertNotNull($captchaInput->attr('aria-label'));
        $this->assertNotSame('', $captchaInput->attr('aria-label'));

        $captcha = $client->getRequest()->getSession()->get('_captcha_public_registration');
        $form = $crawler->filter('button[type=submit]')->form([
            'fos_user_registration[email]' => 'captcha-registration@wallabag.org',
            'fos_user_registration[username]' => 'captcha-registration',
            'fos_user_registration[plainPassword][first]' => 'captcha-password',
            'fos_user_registration[plainPassword][second]' => 'captcha-password',
            'fos_user_registration[captcha]' => $captcha['phrase'],
        ]);

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isRedirect(), trim($crawler->filter('body')->text()));
        $this->assertNotNull($client->getContainer()->get(EntityManagerInterface::class)
            ->getRepository(User::class)
            ->findOneByUsername('captcha-registration'));
    }
}
