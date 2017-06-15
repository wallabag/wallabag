<?php

namespace Tests\Wallabag\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Wallabag\CoreBundle\Command\ReleaseNotificationCommand;

class ReleaseNotificationCommandTest extends WallabagCoreTestCase
{
    public function testRunWithoutArguments()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new ReleaseNotificationCommand());

        $command = $application->find('wallabag:notification:release');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertContains('Sent notification for user admin', $tester->getDisplay());
        $this->assertContains('Finished sending notifications.', $tester->getDisplay());
    }

    public function testRunSendReleaseNotificationCommandWithBadUsername()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new ReleaseNotificationCommand());

        $command = $application->find('wallabag:notification:release');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'unknown',
            'link' => 'https://wallabag.org',
        ]);

        $this->assertContains('User "unknown" not found', $tester->getDisplay());
    }

    public function testSendReleaseNotificationCommandForUser()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new ReleaseNotificationCommand());

        $command = $application->find('wallabag:notification:release');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'admin',
            'link' => 'https://wallabag.org',
        ]);

        $this->assertContains('Sent notification for user admin', $tester->getDisplay());
    }

    public function testSendReleaseNotificationCommand()
    {
        $client = $this->getClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');

        $this->logInAs('admin');

        $notifications = $em->getRepository('WallabagCoreBundle:Notification')->findByUser($this->getLoggedInUserId());

        $this->assertCount(0, $notifications);

        $application = new Application($this->getClient()->getKernel());
        $application->add(new ReleaseNotificationCommand());

        $command = $application->find('wallabag:notification:release');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'admin',
            'link' => 'https://wallabag.org',
        ]);

        $this->assertContains('Sent notification for user admin', $tester->getDisplay());

        $notifications = $em->getRepository('WallabagCoreBundle:Notification')->findByUser($this->getLoggedInUserId());

        $this->assertCount(1, $notifications);

        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('.notifications-area .collection'));
    }
}
