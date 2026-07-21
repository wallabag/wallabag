<?php

namespace Wallabag\Tests\Functional\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Wallabag\Entity\User;
use Wallabag\Tests\Functional\WallabagTestCase;

class CaptchaUserControllerTest extends WallabagTestCase
{
    protected function setUp(): void
    {
        $this->setCaptchaEnabled(true);

        parent::setUp();

        $this->logInAs('admin');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->setCaptchaEnabled(false);
    }

    public function testAdministratorCanCreateUserWithCaptcha(): void
    {
        $client = $this->getTestClient();
        $crawler = $client->request('GET', '/users/new');

        $captchaInput = $crawler->filter('input[name="new_user[captcha]"]');
        $this->assertCount(1, $captchaInput);
        $this->assertCount(0, $crawler->filter('label[for="new_user_captcha"]'));
        $this->assertNotNull($captchaInput->attr('aria-label'));
        $this->assertNotSame('', $captchaInput->attr('aria-label'));

        $client->submit($this->getNewUserForm($crawler, 'captcha-admin', $this->getCaptchaPhrase()));

        $this->assertTrue($client->getResponse()->isRedirect());
        $this->assertNotNull($client->getContainer()->get(EntityManagerInterface::class)
            ->getRepository(User::class)
            ->findOneBy(['username' => 'captcha-admin']));
    }

    public function testIncorrectCaptchaRedisplaysFormWithoutCreatingUser(): void
    {
        $client = $this->getTestClient();
        $crawler = $client->request('GET', '/users/new');
        $initialPhrase = $this->getCaptchaPhrase();

        $crawler = $client->submit($this->getNewUserForm($crawler, 'captcha-admin-incorrect', 'incorrect-answer'));

        $this->assertStringContainsString('validator.captcha_invalid', $client->getResponse()->getContent());
        $this->assertCount(1, $crawler->filter('input[name="new_user[captcha]"]'));
        $this->assertNotSame($initialPhrase, $this->getCaptchaPhrase());
        $this->assertUserDoesNotExist('captcha-admin-incorrect');
    }

    public function testRefreshingCaptchaRejectsPreviousPhraseWithoutCreatingUser(): void
    {
        $client = $this->getTestClient();
        $crawler = $client->request('GET', '/users/new');
        $stalePhrase = $this->getCaptchaPhrase();
        $form = $this->getNewUserForm($crawler, 'captcha-admin-refreshed', $stalePhrase);
        $imageUrl = $crawler->filter('img.captcha_image')->attr('src');

        $client->request('GET', $imageUrl);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $refreshedPhrase = $this->getCaptchaPhrase();
        $this->assertNotSame($stalePhrase, $refreshedPhrase);

        $crawler = $client->submit($form);

        $this->assertStringContainsString('validator.captcha_invalid', $client->getResponse()->getContent());
        $this->assertCount(1, $crawler->filter('input[name="new_user[captcha]"]'));
        $this->assertNotSame($refreshedPhrase, $this->getCaptchaPhrase());
        $this->assertUserDoesNotExist('captcha-admin-refreshed');
    }

    private function getNewUserForm(Crawler $crawler, string $username, string $captcha): Form
    {
        return $crawler->selectButton('user.form.save')->form([
            'new_user[username]' => $username,
            'new_user[email]' => $username . '@wallabag.org',
            'new_user[plainPassword][first]' => 'captcha-password',
            'new_user[plainPassword][second]' => 'captcha-password',
            'new_user[captcha]' => $captcha,
        ]);
    }

    private function getCaptchaPhrase(): string
    {
        $captcha = $this->getTestClient()->getRequest()->getSession()->get('_captcha_administrator_user_creation');

        self::assertIsArray($captcha);
        self::assertArrayHasKey('phrase', $captcha);

        return $captcha['phrase'];
    }

    private function assertUserDoesNotExist(string $username): void
    {
        $user = $this->getTestClient()->getContainer()->get(EntityManagerInterface::class)
            ->getRepository(User::class)
            ->findOneBy(['username' => $username]);

        self::assertNull($user);
    }

    private function setCaptchaEnabled(bool $enabled): void
    {
        $value = $enabled ? '1' : '0';

        putenv('WALLABAG_CAPTCHA_ENABLED=' . $value);
        $_ENV['WALLABAG_CAPTCHA_ENABLED'] = $_SERVER['WALLABAG_CAPTCHA_ENABLED'] = $value;
    }
}
