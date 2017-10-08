<?php

namespace Tests\Wallabag\ImportBundle\Command;

use M6Web\Component\RedisMock\RedisMockFactory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Wallabag\ImportBundle\Command\RedisWorkerCommand;

class RedisWorkerCommandTest extends WallabagCoreTestCase
{
    /**
     * @expectedException \Symfony\Component\Console\Exception\RuntimeException
     * @expectedExceptionMessage Not enough arguments (missing: "serviceName")
     */
    public function testRunRedisWorkerCommandWithoutArguments()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new RedisWorkerCommand());

        $command = $application->find('wallabag:import:redis-worker');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\Exception
     * @expectedExceptionMessage No queue or consumer found for service name
     */
    public function testRunRedisWorkerCommandWithBadService()
    {
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

        $this->assertContains('Worker started at', $tester->getDisplay());
        $this->assertContains('Waiting for message', $tester->getDisplay());
    }
}
