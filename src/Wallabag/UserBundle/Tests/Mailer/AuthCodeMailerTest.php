<?php

namespace Wallabag\UserBundle\Tests\Mailer;

use Wallabag\UserBundle\Entity\User;
use Wallabag\UserBundle\Mailer\AuthCodeMailer;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\DataCollectorTranslator;

/**
 * @see https://www.pmg.com/blog/integration-testing-swift-mailer/
 */
final class CountableMemorySpool extends \Swift_MemorySpool implements \Countable
{
    public function count()
    {
        return count($this->messages);
    }

    public function getMessages()
    {
        return $this->messages;
    }
}

class AuthCodeMailerTest extends \PHPUnit_Framework_TestCase
{
    protected $mailer;
    protected $spool;
    protected $dataCollector;

    protected function setUp()
    {
        $this->spool = new CountableMemorySpool();
        $transport = new \Swift_Transport_SpoolTransport(
            new \Swift_Events_SimpleEventDispatcher(),
            $this->spool
        );
        $this->mailer = new \Swift_Mailer($transport);

        $translator = new Translator('en');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', array(
            'auth_code.mailer.subject' => 'auth_code subject',
            'auth_code.mailer.body' => 'Hi %user%, here is the code: %code% and the support: %support%',
        ), 'en', 'wallabag_user');

        $this->dataCollector = new DataCollectorTranslator($translator);
    }

    public function testSendEmail()
    {
        $user = new User();
        $user->setTwoFactorAuthentication(true);
        $user->setEmailAuthCode(666666);
        $user->setEmail('test@wallabag.io');
        $user->setName('Bob');

        $authCodeMailer = new AuthCodeMailer(
            $this->mailer,
            $this->dataCollector,
            'nobody@test.io',
            'wallabag test',
            'http://0.0.0.0'
        );

        $authCodeMailer->sendAuthCode($user);

        $this->assertCount(1, $this->spool);

        $msg = $this->spool->getMessages()[0];
        $this->assertArrayHasKey('test@wallabag.io', $msg->getTo());
        $this->assertEquals(array('nobody@test.io' => 'wallabag test'), $msg->getFrom());
        $this->assertEquals('auth_code subject', $msg->getSubject());
        $this->assertContains('Hi Bob, here is the code: 666666 and the support: http://0.0.0.0', $msg->toString());
    }
}
