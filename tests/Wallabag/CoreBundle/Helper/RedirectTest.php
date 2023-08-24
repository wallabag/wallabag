<?php

namespace Tests\Wallabag\CoreBundle\Helper;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\CoreBundle\Helper\Redirect;
use Wallabag\UserBundle\Entity\User;

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
            ->willReturn('homepage');

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

        $this->token = new UsernamePasswordToken($this->user, 'password', 'key');
        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($this->token);

        $this->redirect = new Redirect($this->routerMock, $tokenStorage);
    }

    public function testRedirectToNullWithFallback()
    {
        $redirectUrl = $this->redirect->to(null, 'fallback');

        $this->assertSame('fallback', $redirectUrl);
    }

    public function testRedirectToNullWithoutFallback()
    {
        $redirectUrl = $this->redirect->to(null);

        $this->assertSame($this->routerMock->generate('homepage'), $redirectUrl);
    }

    public function testRedirectToValidUrl()
    {
        $redirectUrl = $this->redirect->to('/unread/list');

        $this->assertSame('/unread/list', $redirectUrl);
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

        $this->assertSame($this->routerMock->generate('homepage'), $redirectUrl);
    }

    public function testUserForRedirectWithIgnoreActionMarkAsRead()
    {
        $this->user->getConfig()->setActionMarkAsRead(Config::REDIRECT_TO_HOMEPAGE);

        $redirectUrl = $this->redirect->to('/unread/list', '', true);

        $this->assertSame('/unread/list', $redirectUrl);
    }

    public function testUserForRedirectNullWithFallbackWithIgnoreActionMarkAsRead()
    {
        $this->user->getConfig()->setActionMarkAsRead(Config::REDIRECT_TO_HOMEPAGE);

        $redirectUrl = $this->redirect->to(null, 'fallback', true);

        $this->assertSame('fallback', $redirectUrl);
    }
}
