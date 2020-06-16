<?php

namespace Tests\Wallabag\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Wallabag\CoreBundle\Command\ShowUserCommand;
use Wallabag\UserBundle\Entity\User;

class ShowUserCommandTest extends WallabagCoreTestCase
{
    public function testRunShowUserCommandWithoutUsername()
    {
        $this->expectException(\Symfony\Component\Console\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments');

        $application = new Application($this->getClient()->getKernel());
        $application->add(new ShowUserCommand());

        $command = $application->find('wallabag:user:show');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);
    }

    public function testRunShowUserCommandWithBadUsername()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new ShowUserCommand());

        $command = $application->find('wallabag:user:show');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'unknown',
        ]);

        $this->assertStringContainsString('User "unknown" not found', $tester->getDisplay());
    }

    public function testRunShowUserCommandForUser()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new ShowUserCommand());

        $command = $application->find('wallabag:user:show');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
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
        $client = $this->getClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');

        $this->logInAs('admin');

        /** @var User $user */
        $user = $em->getRepository('WallabagUserBundle:User')->findOneById($this->getLoggedInUserId());

        $user->setName('Bug boss');
        $em->persist($user);

        $em->flush();

        $application = new Application($this->getClient()->getKernel());
        $application->add(new ShowUserCommand());

        $command = $application->find('wallabag:user:show');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'admin',
        ]);

        $this->assertStringContainsString('Display name: Bug boss', $tester->getDisplay());
    }
}
