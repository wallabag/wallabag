<?php

namespace Tests\Wallabag\UserBundle\EventListener;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\CoreBundle\Entity\User;
use Wallabag\UserBundle\EventListener\CreateConfigListener;

class CreateConfigListenerTest extends TestCase
{
    private $em;
    private $listener;
    private $dispatcher;
    private $request;
    private $response;

    protected function setUp(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $this->em = $this->getMockBuilder(EntityManager::class)
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
            $session
        );

        $this->dispatcher = new EventDispatcher();
        $this->dispatcher->addSubscriber($this->listener);

        $this->request = Request::create('/');
        $this->response = Response::create();
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
