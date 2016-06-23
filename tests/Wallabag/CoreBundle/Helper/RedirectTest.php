<?php

namespace Tests\Wallabag\CoreBundle\Helper;

use Wallabag\CoreBundle\Helper\Redirect;

class RedirectTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $routerMock;

    /** @var Redirect */
    private $redirect;

    public function setUp()
    {
        $this->routerMock = $this->getRouterMock();
        $this->redirect = new Redirect($this->routerMock);
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
}
