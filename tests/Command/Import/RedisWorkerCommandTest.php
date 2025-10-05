<?php

namespace Tests\Wallabag\Command\Import;

use M6Web\Component\RedisMock\RedisMockFactory;
use Predis\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\WallabagTestCase;

class RedisWorkerCommandTest extends WallabagTestCase
{
    public function testRunRedisWorkerCommandWithoutArguments()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "serviceName")');

        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:import:redis-worker');

        $tester = new CommandTester($command);
        $tester->execute([]);
    }

    public function testRunRedisWorkerCommandWithBadService()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No queue or consumer found for service name');

        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:import:redis-worker');

        $tester = new CommandTester($command);
        $tester->execute([
            'serviceName' => 'YOMONSERVICE',
        ]);
    }

    public function testRunRedisWorkerCommand()
    {
        $application = new Application($this->getTestClient()->getKernel());

        $factory = new RedisMockFactory();
        $redisMock = $factory->getAdapter(Client::class, true);

        $application->getKernel()->getContainer()->set(Client::class, $redisMock);

        // put a fake message in the queue so the worker will stop after reading that message
        // instead of waiting for others
        $redisMock->lpush('wallabag.import.readability', '{}');

        $command = $application->find('wallabag:import:redis-worker');

        $tester = new CommandTester($command);
        $tester->execute([
            'serviceName' => 'readability',
            '--maxIterations' => 1,
        ]);

        $this->assertStringContainsString('Worker started at', $tester->getDisplay());
        $this->assertStringContainsString('Waiting for message', $tester->getDisplay());
    }
}
