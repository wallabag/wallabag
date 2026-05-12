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
}
