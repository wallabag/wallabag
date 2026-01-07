<?php

namespace Tests\Wallabag\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\User;

class ShowUserCommandTest extends WallabagTestCase
{
    public function testRunShowUserCommandWithoutUsername()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments');

        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:user:show');

        $tester = new CommandTester($command);
        $tester->execute([]);
    }

    public function testRunShowUserCommandWithBadUsername()
    {
        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:user:show');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'unknown',
        ]);

        $this->assertStringContainsString('User "unknown" not found', $tester->getDisplay());
    }

    public function testRunShowUserCommandForUser()
    {
        $application = new Application($this->getTestClient()->getKernel());

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
        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $this->logInAs('admin');

        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneById($this->getLoggedInUserId());

        $user->setName('Bug boss');
        $em->persist($user);

        $em->flush();

        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:user:show');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
        ]);

        $this->assertStringContainsString('Display name: Bug boss', $tester->getDisplay());
    }
}
