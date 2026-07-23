<?php

namespace Wallabag\Tests\Functional\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
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

    public function testIncorrectCaptchaRotatesChallengeBeforeSuccessfulRetry(): void
    {
        $client = $this->getTestClient();
        $crawler = $client->request('GET', '/register/');
        $initialPhrase = $this->getCaptchaPhrase();

        $crawler = $this->submitRegistration($crawler, 'captcha-incorrect', 'incorrect-answer');

        $this->assertStringContainsString('validator.captcha_invalid', $client->getResponse()->getContent());
        $this->assertCount(1, $crawler->filter('input[name="fos_user_registration[captcha]"]'));
        $this->assertUserDoesNotExist('captcha-incorrect');

        $rotatedPhrase = $this->getCaptchaPhrase();
        $this->assertNotSame($initialPhrase, $rotatedPhrase);

        $this->submitRegistration($crawler, 'captcha-incorrect', $rotatedPhrase);

        $this->assertTrue($client->getResponse()->isRedirect());
        $this->assertNotNull($client->getContainer()->get(EntityManagerInterface::class)
            ->getRepository(User::class)
            ->findOneByUsername('captcha-incorrect'));
    }

    public function testRefreshingCaptchaRejectsPreviousPhrase(): void
    {
        $client = $this->getTestClient();
        $crawler = $client->request('GET', '/register/');
        $stalePhrase = $this->getCaptchaPhrase();
        $form = $this->getRegistrationForm($crawler, 'captcha-refreshed', $stalePhrase);
        $imageUrl = $crawler->filter('img.captcha_image')->attr('src');

        $client->request('GET', $imageUrl);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertSame('image/jpeg', $client->getResponse()->headers->get('Content-Type'));
        $refreshedPhrase = $this->getCaptchaPhrase();
        $this->assertNotSame($stalePhrase, $refreshedPhrase);

        $crawler = $client->submit($form);

        $this->assertStringContainsString('validator.captcha_invalid', $client->getResponse()->getContent());
        $this->assertCount(1, $crawler->filter('input[name="fos_user_registration[captcha]"]'));
        $this->assertNotSame($refreshedPhrase, $this->getCaptchaPhrase());
        $this->assertUserDoesNotExist('captcha-refreshed');
    }

    private function submitRegistration(Crawler $crawler, string $username, string $captcha): Crawler
    {
        return $this->getTestClient()->submit($this->getRegistrationForm($crawler, $username, $captcha));
    }

    private function getRegistrationForm(Crawler $crawler, string $username, string $captcha): Form
    {
        return $crawler->filter('button[type=submit]')->form([
            'fos_user_registration[email]' => $username . '@wallabag.org',
            'fos_user_registration[username]' => $username,
            'fos_user_registration[plainPassword][first]' => 'captcha-password',
            'fos_user_registration[plainPassword][second]' => 'captcha-password',
            'fos_user_registration[captcha]' => $captcha,
        ]);
    }

    private function getCaptchaPhrase(): string
    {
        $captcha = $this->getTestClient()->getRequest()->getSession()->get('_captcha_public_registration');

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
}
