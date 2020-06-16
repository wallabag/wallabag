<?php

namespace Tests\Wallabag\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Wallabag\CoreBundle\Command\CleanDuplicatesCommand;
use Wallabag\CoreBundle\Entity\Entry;

class CleanDuplicatesCommandTest extends WallabagCoreTestCase
{
    public function testRunCleanDuplicates()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new CleanDuplicatesCommand());

        $command = $application->find('wallabag:clean-duplicates');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertStringContainsString('Cleaning through 3 user accounts', $tester->getDisplay());
        $this->assertStringContainsString('Finished cleaning. 0 duplicates found in total', $tester->getDisplay());
    }

    public function testRunCleanDuplicatesCommandWithBadUsername()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new CleanDuplicatesCommand());

        $command = $application->find('wallabag:clean-duplicates');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'unknown',
        ]);

        $this->assertStringContainsString('User "unknown" not found', $tester->getDisplay());
    }

    public function testRunCleanDuplicatesCommandForUser()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new CleanDuplicatesCommand());

        $command = $application->find('wallabag:clean-duplicates');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'admin',
        ]);

        $this->assertStringContainsString('Cleaned 0 duplicates for user admin', $tester->getDisplay());
    }

    public function testDuplicate()
    {
        $url = 'https://www.lemonde.fr/sport/visuel/2017/05/05/rondelle-prison-blanchissage-comprendre-le-hockey-sur-glace_5122587_3242.html';
        $client = $this->getClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');

        $this->logInAs('admin');

        $nbEntries = $em->getRepository('WallabagCoreBundle:Entry')->findAllByUrlAndUserId($url, $this->getLoggedInUserId());
        $this->assertCount(0, $nbEntries);

        $user = $em->getRepository('WallabagUserBundle:User')->findOneById($this->getLoggedInUserId());

        $entry1 = new Entry($user);
        $entry1->setUrl($url);

        $entry2 = new Entry($user);
        $entry2->setUrl($url);

        $em->persist($entry1);
        $em->persist($entry2);

        $em->flush();

        $nbEntries = $em->getRepository('WallabagCoreBundle:Entry')->findAllByUrlAndUserId($url, $this->getLoggedInUserId());
        $this->assertCount(2, $nbEntries);

        $application = new Application($this->getClient()->getKernel());
        $application->add(new CleanDuplicatesCommand());

        $command = $application->find('wallabag:clean-duplicates');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'admin',
        ]);

        $this->assertStringContainsString('Cleaned 1 duplicates for user admin', $tester->getDisplay());

        $nbEntries = $em->getRepository('WallabagCoreBundle:Entry')->findAllByUrlAndUserId($url, $this->getLoggedInUserId());
        $this->assertCount(1, $nbEntries);

        $query = $em->createQuery('DELETE FROM Wallabag\CoreBundle\Entity\Entry e WHERE e.url = :url');
        $query->setParameter('url', $url);
        $query->execute();
    }
}
