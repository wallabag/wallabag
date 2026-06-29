<?php

namespace Wallabag\Tests\Integration\Command;

use Craue\ConfigBundle\Util\Config;
use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\Entity\Entry;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class PurgeDeletedEntriesCommandTest extends WallabagKernelTestCase
{
    /** @var list<int> */
    private array $createdIds = [];

    protected function tearDown(): void
    {
        if ($this->createdIds) {
            $em = $this->getEntityManager();
            $em->createQuery('DELETE FROM Wallabag\Entity\Entry e WHERE e.id IN (:ids)')
                ->setParameter('ids', $this->createdIds)
                ->execute();
            $this->createdIds = [];
        }

        static::getContainer()->get(Config::class)->set('deleted_entries_expiration_days', null);

        parent::tearDown();
    }

    public function testNoLifetimeConfigured(): void
    {
        $tester = new CommandTester($this->createApplication()->find('wallabag:purge-deleted-entries'));
        $tester->execute([]);

        $this->assertStringContainsString('No expiration configured', $tester->getDisplay());
    }

    public function testDryRun(): void
    {
        $em = $this->getEntityManager();
        $user = $this->getUser('admin');

        $entry = new Entry($user);
        $entry->setUrl('https://example.com/purge-dry-run-test');
        $entry->updateDeleted(true);
        $em->persist($entry);
        $em->flush();

        $id = $entry->getId();
        $this->createdIds[] = $id;

        $em->getConnection()->executeStatement(
            'UPDATE wallabag_entry SET deleted_at = :date WHERE id = :id',
            ['date' => (new \DateTimeImmutable('-10 days'))->format('Y-m-d H:i:s'), 'id' => $id]
        );

        static::getContainer()->get(Config::class)->set('deleted_entries_expiration_days', '5');

        $tester = new CommandTester($this->createApplication()->find('wallabag:purge-deleted-entries'));
        $tester->execute(['--dry-run' => true]);

        $this->assertStringContainsString('would be purged', $tester->getDisplay());

        $em->clear();
        $this->assertNotNull($em->find(Entry::class, $id));
    }

    public function testPurgesExpiredEntries(): void
    {
        $em = $this->getEntityManager();
        $user = $this->getUser('admin');

        $expired = new Entry($user);
        $expired->setUrl('https://example.com/purge-expired-test');
        $expired->updateDeleted(true);

        $recent = new Entry($user);
        $recent->setUrl('https://example.com/purge-recent-test');
        $recent->updateDeleted(true);

        $em->persist($expired);
        $em->persist($recent);
        $em->flush();

        $expiredId = $expired->getId();
        $this->createdIds[] = $expiredId;
        $recentId = $recent->getId();
        $this->createdIds[] = $recentId;

        $conn = $em->getConnection();
        $conn->executeStatement(
            'UPDATE wallabag_entry SET deleted_at = :date WHERE id = :id',
            ['date' => (new \DateTimeImmutable('-10 days'))->format('Y-m-d H:i:s'), 'id' => $expiredId]
        );
        $conn->executeStatement(
            'UPDATE wallabag_entry SET deleted_at = :date WHERE id = :id',
            ['date' => (new \DateTimeImmutable('-1 day'))->format('Y-m-d H:i:s'), 'id' => $recentId]
        );

        static::getContainer()->get(Config::class)->set('deleted_entries_expiration_days', '5');

        $tester = new CommandTester($this->createApplication()->find('wallabag:purge-deleted-entries'));
        $tester->execute([]);

        $this->assertStringContainsString('purged', $tester->getDisplay());

        $em->clear();
        $this->assertNull($em->find(Entry::class, $expiredId), 'Expired entry should be hard-deleted');
        $this->assertNotNull($em->find(Entry::class, $recentId), 'Recent entry should be kept');
    }

    public function testOlderThanOptionOverridesConfig(): void
    {
        $em = $this->getEntityManager();
        $user = $this->getUser('admin');

        $entry = new Entry($user);
        $entry->setUrl('https://example.com/purge-older-than-test');
        $entry->updateDeleted(true);
        $em->persist($entry);
        $em->flush();

        $id = $entry->getId();
        $this->createdIds[] = $id;

        $em->getConnection()->executeStatement(
            'UPDATE wallabag_entry SET deleted_at = :date WHERE id = :id',
            ['date' => (new \DateTimeImmutable('-20 days'))->format('Y-m-d H:i:s'), 'id' => $id]
        );

        $tester = new CommandTester($this->createApplication()->find('wallabag:purge-deleted-entries'));
        $tester->execute(['--older-than' => '10']);

        $this->assertStringContainsString('purged', $tester->getDisplay());

        $em->clear();
        $this->assertNull($em->find(Entry::class, $id));
    }
}
