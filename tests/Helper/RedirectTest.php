<?php

namespace Tests\Wallabag\Helper;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Wallabag\Entity\Config;
use Wallabag\Entity\User;
use Wallabag\Helper\Redirect;

class RedirectTest extends TestCase
{
    /** @var Router&MockObject */
    private $routerMock;

    /** @var Redirect */
    private $redirect;

    /** @var User */
    private $user;

    /** @var UsernamePasswordToken */
    private $token;

    protected function setUp(): void
    {
        $this->routerMock = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->routerMock->expects($this->any())
            ->method('generate')
            ->with('homepage')
            ->willReturn('/');

        $this->user = new User();
        $this->user->setName('youpi');
        $this->user->setEmail('youpi@youpi.org');
        $this->user->setUsername('youpi');
        $this->user->setPlainPassword('youpi');
        $this->user->setEnabled(true);
        $this->user->addRole('ROLE_SUPER_ADMIN');

        $config = new Config($this->user);
        $config->setItemsPerPage(30);
        $config->setReadingSpeed(200);
        $config->setLanguage('en');
        $config->setPocketConsumerKey('xxxxx');
        $config->setActionMarkAsRead(Config::REDIRECT_TO_CURRENT_PAGE);

        $this->user->setConfig($config);

        $this->token = new UsernamePasswordToken($this->user, 'key');
        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($this->token);

        $this->redirect = new Redirect($this->routerMock, $tokenStorage);
    }

    public function testRedirectToNull()
    {
        $redirectUrl = $this->redirect->to(null);

        $this->assertSame('/', $redirectUrl);
    }

    public function testRedirectToValidUrl()
    {
        $redirectUrl = $this->redirect->to('/unread/list');

        $this->assertSame('/unread/list', $redirectUrl);
    }

    public function testRedirectToAbsoluteUrl()
    {
        $redirectUrl = $this->redirect->to('https://www.google.com/');

        $this->assertSame('/', $redirectUrl);
    }

    public function testWithNotLoggedUser()
    {
        $redirect = new Redirect($this->routerMock, new TokenStorage());
        $redirectUrl = $redirect->to('/unread/list');

        $this->assertSame('/unread/list', $redirectUrl);
    }

    public function testUserForRedirectToHomepage()
    {
        $this->user->getConfig()->setActionMarkAsRead(Config::REDIRECT_TO_HOMEPAGE);

        $redirectUrl = $this->redirect->to('/unread/list');

        $this->assertSame('/', $redirectUrl);
    }

    public function testUserForRedirectWithIgnoreActionMarkAsRead()
    {
        $this->user->getConfig()->setActionMarkAsRead(Config::REDIRECT_TO_HOMEPAGE);

        $redirectUrl = $this->redirect->to('/unread/list', true);

        $this->assertSame('/unread/list', $redirectUrl);
    }

    public function testUserForRedirectNullWithIgnoreActionMarkAsRead()
    {
        $this->user->getConfig()->setActionMarkAsRead(Config::REDIRECT_TO_HOMEPAGE);

        $redirectUrl = $this->redirect->to(null, true);

        $this->assertSame('/', $redirectUrl);
    }
}
