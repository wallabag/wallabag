<?php

namespace Wallabag\Tests\Integration\Command\Import;

use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\Entity\Entry;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class UrlCommandTest extends WallabagKernelTestCase
{
    private $url = 'https://www.20minutes.fr/sport/football/4158082-20250612-euro-espoirs-su-souffrir-ensemble-decimes-bleuets-retiennent-positif-apres-nul-face-portugal';

    public function testRunUrlCommandWithoutArguments(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments');

        $application = $this->createApplication();

        $command = $application->find('wallabag:import:url');

        $tester = new CommandTester($command);
        $tester->execute([]);
    }

    public function testRunUrlCommandWithWrongUsername(): void
    {
        $this->expectException(NoResultException::class);

        $application = $this->createApplication();

        $command = $application->find('wallabag:import:url');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'random',
            'url' => $this->url,
        ]);
    }

    public function testRunUrlCommand(): void
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:import:url');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
            'url' => $this->url,
        ]);

        $this->assertStringContainsString('successfully imported', $tester->getDisplay());
    }

    public function testRunUrlCommandWithTags(): void
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:import:url');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
            'url' => $this->url,
            'tags' => 'sport, football',
        ]);

        $this->assertStringContainsString('successfully imported', $tester->getDisplay());

        $userId = $this->getUser('admin')->getId();
        $entry = $this->getEntityManager()->getRepository(Entry::class)->findByUrlAndUserId($this->url, $userId);
        $this->assertContains('football', $entry->getTagsLabel());
        $this->assertNotContains('basketball', $entry->getTagsLabel());
    }

    public function testRunUrlCommandWithUserId(): void
    {
        $application = $this->createApplication();
        $userId = $this->getUser('admin')->getId();

        $command = $application->find('wallabag:import:url');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => $userId,
            'url' => $this->url,
            '--useUserId' => true,
        ]);

        $this->assertStringContainsString('successfully imported', $tester->getDisplay());
    }
}
