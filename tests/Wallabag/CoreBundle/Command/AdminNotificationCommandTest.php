<?php

namespace Tests\Wallabag\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\CoreBundle\Command\AdminNotificationCommand;
use Wallabag\CoreBundle\Command\CleanDuplicatesCommand;
use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;

class AdminNotificationCommandTest extends WallabagCoreTestCase
{
    /**
     * @expectedException \Symfony\Component\Console\Exception\RuntimeException
     * @expectedExceptionMessage Not enough arguments
     */
    public function testRunWithoutArguments()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new AdminNotificationCommand());

        $command = $application->find('wallabag:notification:send');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);
    }

    public function testRunSendNotificationCommandWithBadUsername()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new AdminNotificationCommand());

        $command = $application->find('wallabag:notification:send');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'unknown',
            'title' => 'foo',
            'message' => 'bar'
        ]);

        $this->assertContains('User "unknown" not found', $tester->getDisplay());
    }

    public function testSendNotificationCommandForUser()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new AdminNotificationCommand());

        $command = $application->find('wallabag:notification:send');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'admin',
            'title' => 'foo',
            'message' => 'bar'
        ]);

        $this->assertContains('Sent notification for user admin', $tester->getDisplay());
    }

    public function testSendNotificationCommand()
    {
        $client = $this->getClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');

        $this->logInAs('admin');

        $notifications = $em->getRepository('WallabagCoreBundle:Notification')->findByUser($this->getLoggedInUserId());

        $this->assertCount(0, $notifications);

        $application = new Application($this->getClient()->getKernel());
        $application->add(new CleanDuplicatesCommand());

        $command = $application->find('wallabag:notification:send');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'admin',
            'title' => 'foo',
            'message' => 'bar'
        ]);

        $this->assertContains('Sent notification for user admin', $tester->getDisplay());

        $notifications = $em->getRepository('WallabagCoreBundle:Notification')->findByUser($this->getLoggedInUserId());

        $this->assertCount(1, $notifications);

        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('.notifications-area .collection'));
    }
}
