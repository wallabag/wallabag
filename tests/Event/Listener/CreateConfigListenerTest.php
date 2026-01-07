<?php

namespace Tests\Wallabag\Event\Listener;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Wallabag\Entity\Config;
use Wallabag\Entity\User;
use Wallabag\Event\Listener\CreateConfigListener;

class CreateConfigListenerTest extends TestCase
{
    private $em;
    private $listener;
    private $dispatcher;
    private $request;
    private $response;
    private $requestStack;

    protected function setUp(): void
    {
        $this->em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestStack = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new CreateConfigListener(
            $this->em,
            20,
            50,
            'fr',
            1,
            1,
            1,
            1,
            $this->requestStack
        );

        $this->dispatcher = new EventDispatcher();
        $this->dispatcher->addSubscriber($this->listener);

        $this->request = Request::create('/');
        $this->response = new Response();
    }

    public function testWithValidUser()
    {
        $user = new User();
        $user->setEnabled(true);

        $event = new FilterUserResponseEvent(
            $user,
            $this->request,
            $this->response
        );

        $config = new Config($user);
        $config->setItemsPerPage(20);
        $config->setFeedLimit(50);
        $config->setLanguage('fr');
        $config->setReadingSpeed(200);

        $this->em->expects($this->once())
            ->method('persist')
            ->willReturn($config);
        $this->em->expects($this->once())
            ->method('flush');

        $this->dispatcher->dispatch(
            $event,
            FOSUserEvents::REGISTRATION_COMPLETED
        );
    }
}
