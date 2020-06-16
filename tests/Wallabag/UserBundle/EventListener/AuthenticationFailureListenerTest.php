<?php

namespace Tests\Wallabag\UserBundle\EventListener;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Wallabag\UserBundle\EventListener\AuthenticationFailureListener;

class AuthenticationFailureListenerTest extends TestCase
{
    private $requestStack;
    private $logHandler;
    private $listener;
    private $dispatcher;

    protected function setUp(): void
    {
        $request = Request::create('/');
        $request->request->set('_username', 'admin');

        $this->requestStack = new RequestStack();
        $this->requestStack->push($request);

        $this->logHandler = new TestHandler();
        $logger = new Logger('test', [$this->logHandler]);

        $this->listener = new AuthenticationFailureListener(
            $this->requestStack,
            $logger
        );

        $this->dispatcher = new EventDispatcher();
        $this->dispatcher->addSubscriber($this->listener);
    }

    public function testOnAuthenticationFailure()
    {
        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $exception = $this->getMockBuilder('Symfony\Component\Security\Core\Exception\AuthenticationException')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new AuthenticationFailureEvent(
            $token,
            $exception
        );

        $this->dispatcher->dispatch(
            AuthenticationEvents::AUTHENTICATION_FAILURE,
            $event
        );

        $records = $this->logHandler->getRecords();

        $this->assertCount(1, $records);
        $this->assertSame('Authentication failure for user "admin", from IP "127.0.0.1", with UA: "Symfony/3.X".', $records[0]['message']);
    }
}
