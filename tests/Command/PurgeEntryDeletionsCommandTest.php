<?php

namespace Tests\Wallabag\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\Entity\EntryDeletion;
use Wallabag\Helper\EntryDeletionExpirationConfig;
use Wallabag\Repository\EntryDeletionRepository;

/**
 * Test the purge entry deletions command.
 *
 * The fixtures set up the following entry deletions:
 * - Admin user: 1 deletion from 4 days ago (entry_id: 1004)
 * - Admin user: 1 deletion from 1 day ago (entry_id: 1001)
 * - Bob user: 1 deletion from 3 days ago (entry_id: 1003)
 */
class PurgeEntryDeletionsCommandTest extends KernelTestCase
{
    private EntryDeletionExpirationConfig $expirationConfig;
    private EntryDeletionRepository $entryDeletionRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->expirationConfig = self::getContainer()->get(EntryDeletionExpirationConfig::class);
        $this->expirationConfig->setExpirationDays(2);

        $em = self::getContainer()->get(EntityManagerInterface::class);
        $this->entryDeletionRepository = $em->getRepository(EntryDeletion::class);
    }

    public function testRunPurgeEntryDeletionsCommandWithDryRun()
    {
        $application = new Application(self::$kernel);
        $command = $application->find('wallabag:purge-entry-deletions');

        $tester = new CommandTester($command);
        $tester->execute([
            '--dry-run' => true,
        ]);

        $this->assertStringContainsString('Dry run mode enabled', $tester->getDisplay());
        $this->assertSame(0, $tester->getStatusCode());

        $count = $this->entryDeletionRepository->countAllBefore($this->expirationConfig->getCutoffDate());
        $this->assertSame(2, $count);
    }

    public function testRunPurgeEntryDeletionsCommand()
    {
        $application = new Application(self::$kernel);
        $command = $application->find('wallabag:purge-entry-deletions');

        $tester = new CommandTester($command);
        $tester->setInputs(['yes']); // confirm deletion
        $tester->execute([]);

        $this->assertStringContainsString('Successfully deleted 2 records', $tester->getDisplay());
        $this->assertSame(0, $tester->getStatusCode());

        $count = $this->entryDeletionRepository->countAllBefore($this->expirationConfig->getCutoffDate());
        $this->assertSame(0, $count);

        $countAll = $this->entryDeletionRepository->countAllBefore(new \DateTime('now'));
        $this->assertSame(1, $countAll);
    }

    public function testRunPurgeEntryDeletionsCommandWithNoRecords()
    {
        $this->expirationConfig->setExpirationDays(10);

        $application = new Application(self::$kernel);
        $command = $application->find('wallabag:purge-entry-deletions');

        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertStringContainsString('No entry deletion records found', $tester->getDisplay());
        $this->assertSame(0, $tester->getStatusCode());
    }
}
