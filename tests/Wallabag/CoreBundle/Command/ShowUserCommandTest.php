<?php

namespace Tests\Wallabag\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Wallabag\CoreBundle\Command\ShowUserCommand;
use Wallabag\UserBundle\Entity\User;

class ShowUserCommandTest extends WallabagCoreTestCase
{
    /**
     * @expectedException \Symfony\Component\Console\Exception\RuntimeException
     * @expectedExceptionMessage Not enough arguments
     */
    public function testRunShowUserCommandWithoutUsername()
    {
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

        $this->assertContains('User "unknown" not found', $tester->getDisplay());
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

        $this->assertContains('Username: admin', $tester->getDisplay());
        $this->assertContains('Email: bigboss@wallabag.org', $tester->getDisplay());
        $this->assertContains('Display name: Big boss', $tester->getDisplay());
        $this->assertContains('2FA (email) activated', $tester->getDisplay());
        $this->assertContains('2FA (OTP) activated', $tester->getDisplay());
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

        $this->assertContains('Display name: Bug boss', $tester->getDisplay());
    }
}
