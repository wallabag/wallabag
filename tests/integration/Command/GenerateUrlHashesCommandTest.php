<?php

namespace Wallabag\Tests\Integration\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\Entity\Entry;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class GenerateUrlHashesCommandTest extends WallabagKernelTestCase
{
    public function testRunGenerateUrlHashesCommand(): void
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:generate-hashed-urls');

        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertStringContainsString('Generating hashed urls for "3" users', $tester->getDisplay());
        $this->assertStringContainsString('Finished generated hashed urls', $tester->getDisplay());
    }

    public function testRunGenerateUrlHashesCommandWithBadUsername(): void
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:generate-hashed-urls');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'unknown',
        ]);

        $this->assertStringContainsString('User "unknown" not found', $tester->getDisplay());
    }

    public function testRunGenerateUrlHashesCommandForUser(): void
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:generate-hashed-urls');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
        ]);

        $this->assertStringContainsString('Generated hashed urls for user: admin', $tester->getDisplay());
    }

    public function testGenerateUrls(): void
    {
        $url = 'http://www.lemonde.fr/sport/visuel/2017/05/05/rondelle-prison-blanchissage-comprendre-le-hockey-sur-glace_5122587_3242.html';
        $em = $this->getEntityManager();
        $user = $this->getUser('admin');

        $entry1 = new Entry($user);
        $entry1->setUrl($url);

        $em->persist($entry1);
        $em->flush();

        $application = $this->createApplication();

        $command = $application->find('wallabag:generate-hashed-urls');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
        ]);

        $this->assertStringContainsString('Generated hashed urls for user: admin', $tester->getDisplay());

        $entry = $em->getRepository(Entry::class)->findOneByUrl($url);
        $this->assertInstanceOf(Entry::class, $entry);

        $this->assertSame($entry->getHashedUrl(), hash('sha1', $url));

        $query = $em->createQuery('DELETE FROM Wallabag\Entity\Entry e WHERE e.url = :url');
        $query->setParameter('url', $url);
        $query->execute();
    }
}
