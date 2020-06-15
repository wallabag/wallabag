<?php

namespace Tests\Wallabag\UserBundle\Mailer;

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Wallabag\UserBundle\Entity\User;
use Wallabag\UserBundle\Mailer\AuthCodeMailer;

class AuthCodeMailerTest extends TestCase
{
    protected $mailer;
    protected $spool;
    protected $twig;

    protected function setUp(): void
    {
        $this->spool = new CountableMemorySpool();
        $transport = new \Swift_Transport_SpoolTransport(
            new \Swift_Events_SimpleEventDispatcher(),
            $this->spool
        );
        $this->mailer = new \Swift_Mailer($transport);

        $twigTemplate = <<<'TWIG'
{% block subject %}subject{% endblock %}
{% block body_html %}html body {{ code }}{% endblock %}
{% block body_text %}text body {{ support_url }}{% endblock %}
TWIG;

        $this->twig = new Environment(new ArrayLoader(['WallabagUserBundle:TwoFactor:email_auth_code.html.twig' => $twigTemplate]));
    }

    public function testSendEmail()
    {
        $user = new User();
        $user->setEmailTwoFactor(true);
        $user->setEmailAuthCode(666666);
        $user->setEmail('test@wallabag.io');
        $user->setName('Bob');

        $authCodeMailer = new AuthCodeMailer(
            $this->mailer,
            $this->twig,
            'nobody@test.io',
            'wallabag test',
            'http://0.0.0.0/support',
            'http://0.0.0.0/'
        );

        $authCodeMailer->sendAuthCode($user);

        $this->assertCount(1, $this->spool);

        $msg = $this->spool->getMessages()[0];
        $this->assertArrayHasKey('test@wallabag.io', $msg->getTo());
        $this->assertSame(['nobody@test.io' => 'wallabag test'], $msg->getFrom());
        $this->assertSame('subject', $msg->getSubject());
        $this->assertStringContainsString('text body http://0.0.0.0/support', $msg->toString());
        $this->assertStringContainsString('html body 666666', $msg->toString());
    }
}
