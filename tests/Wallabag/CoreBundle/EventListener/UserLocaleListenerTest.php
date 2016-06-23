<?php

namespace Tests\Wallabag\CoreBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\CoreBundle\EventListener\UserLocaleListener;
use Wallabag\UserBundle\Entity\User;

class UserLocaleListenerTest extends \PHPUnit_Framework_TestCase
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
