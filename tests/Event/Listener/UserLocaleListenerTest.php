<?php

namespace Tests\Wallabag\Event\Listener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Wallabag\Entity\Config;
use Wallabag\Entity\User;
use Wallabag\Event\Listener\UserLocaleListener;

class UserLocaleListenerTest extends TestCase
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

        $userToken = new UsernamePasswordToken($user, 'test');
        $request = Request::create('/');
        $event = new InteractiveLoginEvent($request, $userToken);

        $listener->onInteractiveLogin($event);

        $this->assertSame('fr', $session->get('_locale'));
    }

    public function testWithoutLanguage()
    {
        $session = new Session(new MockArraySessionStorage());
        $listener = new UserLocaleListener($session);

        $user = new User();
        $user->setEnabled(true);

        $config = new Config($user);

        $user->setConfig($config);

        $userToken = new UsernamePasswordToken($user, 'test');
        $request = Request::create('/');
        $event = new InteractiveLoginEvent($request, $userToken);

        $listener->onInteractiveLogin($event);

        $this->assertNull($session->get('_locale'));
    }

    public function testWithLanguageFromSession()
    {
        $session = new Session(new MockArraySessionStorage());
        $listener = new UserLocaleListener($session);
        $session->set('_locale', 'de');

        $user = new User();
        $user->setEnabled(true);

        $config = new Config($user);
        $config->setLanguage('fr');

        $user->setConfig($config);

        $userToken = new UsernamePasswordToken($user, 'test');
        $request = Request::create('/');
        $event = new InteractiveLoginEvent($request, $userToken);

        $listener->onInteractiveLogin($event);

        $this->assertSame('de', $session->get('_locale'));
    }
}
