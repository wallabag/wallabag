<?php

namespace Wallabag\CoreBundle\Tests\EventListener;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Wallabag\CoreBundle\EventListener\UserLocaleListener;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\UserBundle\Entity\User;

class UserLocaleListenerTest extends KernelTestCase
{
    public function testWithLanguage()
    {
        $session = new Session(new MockArraySessionStorage());
        $listener = new UserLocaleListener($session);

        $user = new User();
        $user->setEnabled(true);

        $config = new Config($user);
        $config->setLanguage('fr');

        $user->setConfig($config);

        $userToken = new UsernamePasswordToken($user, '', 'test');
        $request = Request::create('/');
        $event = new InteractiveLoginEvent($request, $userToken);

        $listener->onInteractiveLogin($event);

        $this->assertEquals('fr', $session->get('_locale'));
    }

    public function testWithoutLanguage()
    {
        $session = new Session(new MockArraySessionStorage());
        $listener = new UserLocaleListener($session);

        $user = new User();
        $user->setEnabled(true);

        $config = new Config($user);

        $user->setConfig($config);

        $userToken = new UsernamePasswordToken($user, '', 'test');
        $request = Request::create('/');
        $event = new InteractiveLoginEvent($request, $userToken);

        $listener->onInteractiveLogin($event);

        $this->assertEquals('', $session->get('_locale'));
    }
}
