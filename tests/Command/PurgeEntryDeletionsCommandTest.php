<?php

namespace Tests\Wallabag\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\EntryDeletion;

/**
 * Test the purge entry deletions command.
 * 
 * The fixtures set up the following entry deletions:
 * - Admin user: 1 deletion from 4 days ago (entry_id: 1004)
 * - Admin user: 1 deletion from 1 day ago (entry_id: 1001)
 * - Bob user: 1 deletion from 3 days ago (entry_id: 1003)
 */
class PurgeEntryDeletionsCommandTest extends WallabagTestCase
{
    public function testRunPurgeEntryDeletionsCommandWithDryRun()
    {
        $application = new Application($this->getTestClient()->getKernel());
        $command = $application->find('wallabag:purge-entry-deletions');
        $dateStr = '-2 days';

        $tester = new CommandTester($command);
        $tester->execute([
            '--older-than' => $dateStr,
            '--dry-run' => true,
        ]);

        $this->assertStringContainsString('Dry run mode enabled', $tester->getDisplay());
        $this->assertSame(0, $tester->getStatusCode());

        $em = $this->getEntityManager();
        $count = $em->getRepository(EntryDeletion::class)->countAllBefore(new \DateTime($dateStr));
        $this->assertSame(2, $count);
    }

    public function testRunPurgeEntryDeletionsCommand()
    {
        $application = new Application($this->getTestClient()->getKernel());
        $command = $application->find('wallabag:purge-entry-deletions');
        $dateStr = '-2 days';

        $tester = new CommandTester($command);
        $tester->setInputs(['yes']); // confirm deletion
        $tester->execute(['--older-than' => $dateStr]);

        $this->assertStringContainsString('Successfully deleted 2 records', $tester->getDisplay());
        $this->assertSame(0, $tester->getStatusCode());

        $em = $this->getEntityManager();

        $count = $em->getRepository(EntryDeletion::class)->countAllBefore(new \DateTime($dateStr));
        $this->assertSame(0, $count);

        $count = $em->getRepository(EntryDeletion::class)->countAllBefore(new \DateTime('now'));
        $this->assertSame(1, $count);
    }

    public function testRunPurgeEntryDeletionsCommandWithNoRecords()
    {
        $application = new Application($this->getTestClient()->getKernel());
        $command = $application->find('wallabag:purge-entry-deletions');

        $tester = new CommandTester($command);
        $tester->execute([
            '--older-than' => '-1 year',
        ]);

        $this->assertStringContainsString('No entry deletion records found', $tester->getDisplay());
        $this->assertSame(0, $tester->getStatusCode());
    }
}
