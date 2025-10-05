<?php

namespace Tests\Wallabag\Event\Listener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Wallabag\Event\Listener\LocaleListener;

class LocaleListenerTest extends TestCase
{
    public function testWithoutSession()
    {
        $request = Request::create('/');

        $listener = new LocaleListener('fr');
        $event = $this->getEvent($request);

        $listener->onKernelRequest($event);
        $this->assertSame('en', $request->getLocale());
    }

    public function testWithPreviousSession()
    {
        $request = Request::create('/');
        // generate a previous session
        $request->cookies->set('MOCKSESSID', 'foo');
        $request->setSession(new Session(new MockArraySessionStorage()));

        $listener = new LocaleListener('fr');
        $event = $this->getEvent($request);

        $listener->onKernelRequest($event);
        $this->assertSame('fr', $request->getLocale());
    }

    public function testLocaleFromRequestAttribute()
    {
        $request = Request::create('/');
        // generate a previous session
        $request->cookies->set('MOCKSESSID', 'foo');
        $request->setSession(new Session(new MockArraySessionStorage()));
        $request->attributes->set('_locale', 'es');

        $listener = new LocaleListener('fr');
        $event = $this->getEvent($request);

        $listener->onKernelRequest($event);
        $this->assertSame('en', $request->getLocale());
        $this->assertSame('es', $request->getSession()->get('_locale'));
    }

    public function testSubscribedEvents()
    {
        $request = Request::create('/');
        // generate a previous session
        $request->cookies->set('MOCKSESSID', 'foo');
        $request->setSession(new Session(new MockArraySessionStorage()));

        $listener = new LocaleListener('fr');
        $event = $this->getEvent($request);

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($listener);

        $dispatcher->dispatch(
            $event,
            KernelEvents::REQUEST
        );

        $this->assertSame('fr', $request->getLocale());
    }

    private function getEvent(Request $request)
    {
        $kernel = $this->getMockBuilder(HttpKernelInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        return new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
    }
}
