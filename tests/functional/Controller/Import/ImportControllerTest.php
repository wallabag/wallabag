<?php

namespace Wallabag\Tests\Functional\Controller\Import;

use Craue\ConfigBundle\Util\Config;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Predis\Client;
use Predis\Command\CommandInterface;
use Wallabag\Tests\Functional\WallabagTestCase;

class ImportControllerTest extends WallabagTestCase
{
    public function testLogin(): void
    {
        $client = $this->getTestClient();

        $client->request('GET', '/import/');

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('login', $client->getResponse()->headers->get('location'));
    }

    public function testImportList(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(14, $crawler->filter('.card-title')->count());
    }

    public function testDisabledImportersAreHiddenAndNotFound(): void
    {
        $_SERVER['WALLABAG_DISABLED_IMPORTERS'] = $_ENV['WALLABAG_DISABLED_IMPORTERS'] = 'pinboard, delicious';

        try {
            $client = $this->getNewClient();
            $this->logInAs('admin');

            $crawler = $client->request('GET', '/import/');
            $this->assertSame(200, $client->getResponse()->getStatusCode());
            $this->assertSame(12, $crawler->filter('.card-title')->count());
            $this->assertCount(0, $crawler->filter('a[href="/import/pinboard"]'));
            $this->assertCount(0, $crawler->filter('a[href="/import/delicious"]'));
            $this->assertCount(1, $crawler->filter('a[href="/import/readability"]'));

            $client->request('GET', '/import/pinboard');
            $this->assertSame(404, $client->getResponse()->getStatusCode());

            $client->request('GET', '/import/delicious');
            $this->assertSame(404, $client->getResponse()->getStatusCode());

            $client->request('GET', '/import/readability');
            $this->assertSame(200, $client->getResponse()->getStatusCode());
        } finally {
            unset($_SERVER['WALLABAG_DISABLED_IMPORTERS'], $_ENV['WALLABAG_DISABLED_IMPORTERS']);
        }
    }

    public function testRabbitMqTotalIncludesFeedImports(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $container = $client->getContainer();
        $message = new AMQPMessage();
        $message->setMessageCount(2);
        $channel = $this->createMock(AMQPChannel::class);
        $channel->method('basic_get')->willReturnCallback(
            static fn (string $queue): ?AMQPMessage => 'wallabag.import.feeds' === $queue ? $message : null,
        );
        $connection = $this->createMock(AMQPStreamConnection::class);
        $connection->method('channel')->willReturn($channel);
        $container->set('old_sound_rabbit_mq.connection.default', $connection);
        $config = $container->get(Config::class);
        $config->set('import_with_rabbitmq', '1');

        try {
            $crawler = $client->request('GET', '/import/');
            self::assertResponseIsSuccessful();
            self::assertSame('Messages in queue: 3', $crawler->filter('p[data-controller="materialize--toast"]')->text());
        } finally {
            $config->set('import_with_rabbitmq', '0');
        }
    }

    public function testRedisTotalIncludesFeedImports(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $container = $client->getContainer();
        $redis = $this->getMockBuilder(Client::class)->onlyMethods(['executeCommand'])->getMock();
        $redis->method('executeCommand')->willReturnCallback(
            static fn (CommandInterface $command): int => 'LLEN' === $command->getId()
                && ['wallabag.import.feeds'] === $command->getArguments() ? 4 : 0,
        );
        $container->set(Client::class, $redis);
        $config = $container->get(Config::class);
        $config->set('import_with_redis', '1');

        try {
            $crawler = $client->request('GET', '/import/');
            self::assertResponseIsSuccessful();
            self::assertSame('Messages in queue: 4', $crawler->filter('p[data-controller="materialize--toast"]')->text());
        } finally {
            $config->set('import_with_redis', '0');
        }
    }

    public function testRedisTotalIgnoresDisabledImporters(): void
    {
        $_SERVER['WALLABAG_DISABLED_IMPORTERS'] = $_ENV['WALLABAG_DISABLED_IMPORTERS'] = 'pinboard';

        try {
            $client = $this->getNewClient();
            $this->logInAs('admin');
            $container = $client->getContainer();
            $redis = $this->getMockBuilder(Client::class)->onlyMethods(['executeCommand'])->getMock();
            $redis->method('executeCommand')->willReturnCallback(
                static fn (CommandInterface $command): int => 'LLEN' !== $command->getId() ? 0 : match ($command->getArguments()) {
                    ['wallabag.import.pinboard'] => 10,
                    ['wallabag.import.readability'] => 2,
                    default => 0,
                },
            );
            $container->set(Client::class, $redis);
            $config = $container->get(Config::class);
            $config->set('import_with_redis', '1');

            try {
                $crawler = $client->request('GET', '/import/');
                self::assertResponseIsSuccessful();
                self::assertSame('Messages in queue: 2', $crawler->filter('p[data-controller="materialize--toast"]')->text());
            } finally {
                $config->set('import_with_redis', '0');
            }
        } finally {
            unset($_SERVER['WALLABAG_DISABLED_IMPORTERS'], $_ENV['WALLABAG_DISABLED_IMPORTERS']);
        }
    }
}
