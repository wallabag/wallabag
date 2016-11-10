<?php

namespace Tests\Wallabag\CoreBundle\Helper;

use Wallabag\CoreBundle\Helper\Redirect;

class RedirectTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $routerMock;

    /** @var Redirect */
    private $redirect;

    const PASSWORD = 's3Cr3t';
    const SALT = '^S4lt$';

    public function setUp()
    {
        $this->routerMock = $this->getRouterMock();
        $user = $this->createUser();
        $tokenStorage = $this->createTokenStorage($user);
        $this->redirect = new Redirect($this->routerMock, $tokenStorage);
    }

    public function testRedirectToNullWithFallback()
    {
        $redirectUrl = $this->redirect->to(null, 'fallback');

        $this->assertEquals('fallback', $redirectUrl);
    }

    public function testRedirectToNullWithoutFallback()
    {
        $redirectUrl = $this->redirect->to(null);

        $this->assertEquals($this->routerMock->generate('homepage'), $redirectUrl);
    }

    public function testRedirectToValidUrl()
    {
        $redirectUrl = $this->redirect->to('/unread/list');

        $this->assertEquals('/unread/list', $redirectUrl);
    }

    private function getRouterMock()
    {
        $mock = $this->getMockBuilder('Symfony\Component\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('generate')
            ->with('homepage')
            ->willReturn('homepage');

        return $mock;
    }

    protected function createTokenStorage($user = null)
    {
        $token = $this->createAuthenticationToken($user);

        $mock = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($token))
        ;

        return $mock;
    }

    protected function createUser()
    {
        $mock = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('getPassword')
            ->will($this->returnValue(static::PASSWORD))
        ;

        $mock
            ->expects($this->any())
            ->method('getSalt')
            ->will($this->returnValue(static::SALT))
        ;

        return $mock;
    }

    protected function createAuthenticationToken($user = null)
    {
        $mock = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user))
        ;

        return $mock;
    }
}
