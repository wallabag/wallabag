<?php

namespace Tests\Wallabag\UserBundle\EventListener;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\UserBundle\Entity\User;
use Wallabag\UserBundle\EventListener\CreateConfigListener;

class CreateConfigListenerTest extends TestCase
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

        $this->listener = new CreateConfigListener(
            $this->em,
            'baggy',
            20,
            50,
            'fr',
            1,
            1,
            1
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
        $config->setTheme('baggy');
        $config->setItemsPerPage(20);
        $config->setRssLimit(50);
        $config->setLanguage('fr');
        $config->setReadingSpeed(1);

        $this->em->expects($this->once())
            ->method('persist')
            ->will($this->returnValue($config));
        $this->em->expects($this->once())
            ->method('flush');

        $this->dispatcher->dispatch(
            FOSUserEvents::REGISTRATION_COMPLETED,
            $event
        );
    }
}
