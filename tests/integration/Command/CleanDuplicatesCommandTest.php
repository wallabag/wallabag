<?php

namespace Wallabag\Tests\Integration\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\Entity\Entry;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class CleanDuplicatesCommandTest extends WallabagKernelTestCase
{
    public function testRunCleanDuplicates(): void
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:clean-duplicates');

        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertStringContainsString('Cleaning through 3 user accounts', $tester->getDisplay());
        $this->assertStringContainsString('Finished cleaning. 0 duplicates found in total', $tester->getDisplay());
    }

    public function testRunCleanDuplicatesCommandWithBadUsername(): void
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:clean-duplicates');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'unknown',
        ]);

        $this->assertStringContainsString('User "unknown" not found', $tester->getDisplay());
    }

    public function testRunCleanDuplicatesCommandForUser(): void
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:clean-duplicates');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
        ]);

        $this->assertStringContainsString('Cleaned 0 duplicates for user admin', $tester->getDisplay());
    }

    public function testDuplicate(): void
    {
        $url = 'https://www.lemonde.fr/sport/visuel/2017/05/05/rondelle-prison-blanchissage-comprendre-le-hockey-sur-glace_5122587_3242.html';
        $em = $this->getEntityManager();
        $user = $this->getUser('admin');
        $userId = $user->getId();

        $nbEntries = $em->getRepository(Entry::class)->findAllByUrlAndUserId($url, $userId);
        $this->assertCount(0, $nbEntries);

        $entry1 = new Entry($user);
        $entry1->setUrl($url);

        $entry2 = new Entry($user);
        $entry2->setUrl($url);

        $em->persist($entry1);
        $em->persist($entry2);

        $em->flush();

        $nbEntries = $em->getRepository(Entry::class)->findAllByUrlAndUserId($url, $userId);
        $this->assertCount(2, $nbEntries);

        $application = $this->createApplication();

        $command = $application->find('wallabag:clean-duplicates');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
        ]);

        $this->assertStringContainsString('Cleaned 1 duplicates for user admin', $tester->getDisplay());

        $nbEntries = $em->getRepository(Entry::class)->findAllByUrlAndUserId($url, $userId);
        $this->assertCount(1, $nbEntries);

        $query = $em->createQuery('DELETE FROM Wallabag\Entity\Entry e WHERE e.url = :url');
        $query->setParameter('url', $url);
        $query->execute();
    }

    public function testDuplicateSkipsAlreadyDeletedEntry(): void
    {
        $url = 'https://www.lemonde.fr/sport/visuel/2017/05/05/rondelle-prison-blanchissage-comprendre-le-hockey-sur-glace_5122587_3242.html';
        $em = $this->getEntityManager();
        $user = $this->getUser('admin');

        // Deletion and restoration can combine in a lot of different ways so any order is possible.
        // This case covers most of the edge cases: deleted / live / deleted.

        $entryDeletedBefore = new Entry($user);
        $entryDeletedBefore->setUrl($url);
        $entryDeletedBefore->updateDeleted(true);
        $deletedAtBefore = $entryDeletedBefore->getDeletedAt()->format('U');

        $entryLive = new Entry($user);
        $entryLive->setUrl($url);

        $entryDeletedAfter = new Entry($user);
        $entryDeletedAfter->setUrl($url);
        $entryDeletedAfter->updateDeleted(true);
        $deletedAtAfter = $entryDeletedAfter->getDeletedAt()->format('U');

        $em->persist($entryDeletedBefore);
        $em->persist($entryLive);
        $em->persist($entryDeletedAfter);

        $em->flush();

        $application = $this->createApplication();

        $command = $application->find('wallabag:clean-duplicates');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
        ]);

        $this->assertStringContainsString('Cleaned 0 duplicates for user admin', $tester->getDisplay());

        $em->refresh($entryDeletedBefore);
        $this->assertSame($deletedAtBefore, $entryDeletedBefore->getDeletedAt()->format('U'));

        $em->refresh($entryDeletedAfter);
        $this->assertSame($deletedAtAfter, $entryDeletedAfter->getDeletedAt()->format('U'));

        $em->refresh($entryLive);
        $this->assertFalse($entryLive->isDeleted());

        $query = $em->createQuery('DELETE FROM Wallabag\Entity\Entry e WHERE e.url = :url');
        $query->setParameter('url', $url);
        $query->execute();
    }
}
