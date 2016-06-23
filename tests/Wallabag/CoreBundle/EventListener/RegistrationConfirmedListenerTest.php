<?php

namespace Tests\Wallabag\CoreBundle\EventListener;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\CoreBundle\EventListener\RegistrationConfirmedListener;
use Wallabag\UserBundle\Entity\User;

class RegistrationConfirmedListenerTest extends \PHPUnit_Framework_TestCase
{
    private $em;
    private $listener;
    private $dispatcher;
    private $request;
    private $response;

    protected function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new RegistrationConfirmedListener(
            $this->em,
            'baggy',
            20,
            50,
            'fr'
        );

        $this->dispatcher = new EventDispatcher();
        $this->dispatcher->addSubscriber($this->listener);

        $this->request = Request::create('/');
        $this->response = Response::create();
    }

    public function testWithInvalidUser()
    {
        $user = new User();
        $user->setEnabled(false);

        $event = new FilterUserResponseEvent(
            $user,
            $this->request,
            $this->response
        );

        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->never())->method('flush');

        $this->dispatcher->dispatch(
            FOSUserEvents::REGISTRATION_CONFIRMED,
            $event
        );
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
        $config->setTheme('baggy');
        $config->setItemsPerPage(20);
        $config->setRssLimit(50);
        $config->setLanguage('fr');

        $this->em->expects($this->once())
            ->method('persist')
            ->will($this->returnValue($config));
        $this->em->expects($this->once())
            ->method('flush');

        $this->dispatcher->dispatch(
            FOSUserEvents::REGISTRATION_CONFIRMED,
            $event
        );
    }
}
