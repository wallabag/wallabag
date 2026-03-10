<?php

namespace Wallabag\Tests\Integration\Command;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class ShowUserCommandTest extends WallabagKernelTestCase
{
    public function testRunShowUserCommandWithoutUsername()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments');

        $application = $this->createApplication();

        $command = $application->find('wallabag:user:show');

        $tester = new CommandTester($command);
        $tester->execute([]);
    }

    public function testRunShowUserCommandWithBadUsername()
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:user:show');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'unknown',
        ]);

        $this->assertStringContainsString('User "unknown" not found', $tester->getDisplay());
    }

    public function testRunShowUserCommandForUser()
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:user:show');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
        ]);

        $this->assertStringContainsString('Username: admin', $tester->getDisplay());
        $this->assertStringContainsString('Email: bigboss@wallabag.org', $tester->getDisplay());
        $this->assertStringContainsString('Display name: Big boss', $tester->getDisplay());
        $this->assertStringContainsString('2FA (email) activated', $tester->getDisplay());
        $this->assertStringContainsString('2FA (OTP) activated', $tester->getDisplay());
    }

    public function testShowUser()
    {
        $em = $this->getEntityManager();
        $user = $this->getUser('admin');

        $user->setName('Bug boss');
        $em->persist($user);

        $em->flush();

        $application = $this->createApplication();

        $command = $application->find('wallabag:user:show');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
        ]);

        $this->assertStringContainsString('Display name: Bug boss', $tester->getDisplay());
    }
}
