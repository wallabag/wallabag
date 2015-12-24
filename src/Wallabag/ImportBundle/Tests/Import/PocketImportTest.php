<?php

namespace Wallabag\ImportBundle\Tests\Import;

use Wallabag\UserBundle\Entity\User;
use Wallabag\ImportBundle\Import\PocketImport;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;

class PocketImportTest extends \PHPUnit_Framework_TestCase
{
    protected $token;
    protected $user;
    protected $session;
    protected $em;

    private function getPocketImport($consumerKey = 'ConsumerKey')
    {
        $this->user = new User();

        $this->tokenStorage = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->session = new Session(new MockArraySessionStorage());

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        return new PocketImport(
            $this->tokenStorage,
            $this->session,
            $this->em,
            $consumerKey
        );
    }

    public function testInit()
    {
        $pocketImport = $this->getPocketImport();

        $this->assertEquals('Pocket', $pocketImport->getName());
        $this->assertEquals('This importer will import all your <a href="https://getpocket.com">Pocket</a> data.', $pocketImport->getDescription());
    }

    public function testOAuthRequest()
    {
        $client = new Client();

        $mock = new Mock([
            new Response(200, ['Content-Type' => 'application/json'], Stream::factory(json_encode(['code' => 'wunderbar']))),
        ]);

        $client->getEmitter()->attach($mock);

        $pocketImport = $this->getPocketImport();
        $pocketImport->setClient($client);

        $url = $pocketImport->oAuthRequest('http://0.0.0.0./redirect', 'http://0.0.0.0./callback');

        $this->assertEquals('https://getpocket.com/auth/authorize?request_token=wunderbar&redirect_uri=http://0.0.0.0./callback', $url);
        $this->assertEquals('wunderbar', $this->session->get('pocketCode'));
    }

    public function testOAuthAuthorize()
    {
        $client = new Client();

        $mock = new Mock([
            new Response(200, ['Content-Type' => 'application/json'], Stream::factory(json_encode(['access_token' => 'wunderbar']))),
        ]);

        $client->getEmitter()->attach($mock);

        $pocketImport = $this->getPocketImport();
        $pocketImport->setClient($client);

        $accessToken = $pocketImport->oAuthAuthorize();

        $this->assertEquals('wunderbar', $accessToken);
    }

    public function testImport()
    {
        $client = new Client();

        $mock = new Mock([
            new Response(200, ['Content-Type' => 'application/json'], Stream::factory(json_encode(['list' => []]))),
        ]);

        $client->getEmitter()->attach($mock);

        $pocketImport = $this->getPocketImport();
        $pocketImport->setClient($client);

        $pocketImport->import('wunderbar');

        $this->assertEquals('0 entries imported, 0 already saved.', $this->session->getFlashBag()->get('notice')[0]);
    }
}
