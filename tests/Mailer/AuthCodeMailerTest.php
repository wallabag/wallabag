<?php

namespace Tests\Wallabag\Mailer;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Wallabag\Entity\User;
use Wallabag\Mailer\AuthCodeMailer;

class AuthCodeMailerTest extends TestCase
{
    protected $twig;

    protected function setUp(): void
    {
        $twigTemplate = <<<'TWIG'
{% block subject %}subject{% endblock %}
{% block body_html %}html body {{ code }}{% endblock %}
{% block body_text %}text body {{ support_url }}{% endblock %}
TWIG;

        $this->twig = new Environment(new ArrayLoader(['TwoFactor/email_auth_code.html.twig' => $twigTemplate]));
    }

    public function testSendEmail()
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function ($email) {
                $this->assertSame('subject', $email->getSubject());
                $this->assertSame('text body http://0.0.0.0/support', $email->getTextBody());
                $this->assertSame('html body 666666', $email->getHtmlBody());

                $this->assertCount(1, $email->getTo());
                /** @var Address[] $addresses */
                $addresses = $email->getTo();
                $this->assertInstanceOf(Address::class, $addresses[0]);
                $this->assertSame('', $addresses[0]->getName());
                $this->assertSame('test@wallabag.io', $addresses[0]->getAddress());

                $this->assertCount(1, $email->getFrom());
                /** @var Address[] $addresses */
                $addresses = $email->getFrom();
                $this->assertInstanceOf(Address::class, $addresses[0]);
                $this->assertSame('wallabag test', $addresses[0]->getName());
                $this->assertSame('nobody@test.io', $addresses[0]->getAddress());

                return true;
            }));

        $user = new User();
        $user->setEmailTwoFactor(true);
        $user->setEmailAuthCode('666666');
        $user->setEmail('test@wallabag.io');
        $user->setName('Bob');

        $authCodeMailer = new AuthCodeMailer(
            $mailer,
            $this->twig,
            'nobody@test.io',
            'wallabag test',
            'http://0.0.0.0/support'
        );

        $authCodeMailer->sendAuthCode($user);
    }
}
