<?php

namespace Wallabag\CoreBundle\Tests\Helper;

use Wallabag\CoreBundle\Helper\Redirect;

class RedirectTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Symfony\Component\Routing\Router */
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
        return $this->getMockBuilder('Symfony\Component\Routing\Router')
            ->setMethods(['generate'])
            ->disableOriginalConstructor()
            ->getMock();
    }
}
