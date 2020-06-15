<?php

namespace Tests\Wallabag\ImportBundle\Command;

use M6Web\Component\RedisMock\RedisMockFactory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Wallabag\ImportBundle\Command\RedisWorkerCommand;

class RedisWorkerCommandTest extends WallabagCoreTestCase
{
    public function testRunRedisWorkerCommandWithoutArguments()
    {
        $this->expectException(\Symfony\Component\Console\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "serviceName")');

        $application = new Application($this->getClient()->getKernel());
        $application->add(new RedisWorkerCommand());

        $command = $application->find('wallabag:import:redis-worker');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);
    }

    public function testRunRedisWorkerCommandWithBadService()
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\Exception::class);
        $this->expectExceptionMessage('No queue or consumer found for service name');

        $application = new Application($this->getClient()->getKernel());
        $application->add(new RedisWorkerCommand());

        $command = $application->find('wallabag:import:redis-worker');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'serviceName' => 'YOMONSERVICE',
        ]);
    }

    public function testRunRedisWorkerCommand()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new RedisWorkerCommand());

        $factory = new RedisMockFactory();
        $redisMock = $factory->getAdapter('Predis\Client', true);

        $application->getKernel()->getContainer()->set('wallabag_core.redis.client', $redisMock);

        // put a fake message in the queue so the worker will stop after reading that message
        // instead of waiting for others
        $redisMock->lpush('wallabag.import.readability', '{}');

        $command = $application->find('wallabag:import:redis-worker');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'serviceName' => 'readability',
            '--maxIterations' => 1,
        ]);

        $this->assertStringContainsString('Worker started at', $tester->getDisplay());
        $this->assertStringContainsString('Waiting for message', $tester->getDisplay());
    }
}
