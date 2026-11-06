<?php

namespace Wallabag\Tests\Integration\Command\Import;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\Entity\Tag;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class ImportCommandTest extends WallabagKernelTestCase
{
    public function testRunImportCommandWithoutArguments(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments');

        $application = $this->createApplication();

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([]);
    }

    public function testRunImportCommandWithoutFilepath(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('not found');

        $application = $this->createApplication();

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
            'filepath' => 1,
        ]);
    }

    public function testRunImportCommandWithWrongUsername(): void
    {
        $this->expectException(NoResultException::class);

        $application = $this->createApplication();

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'random',
            'filepath' => './',
        ]);
    }

    public function testRunImportCommand(): void
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
            'filepath' => static::getContainer()->getParameter('kernel.project_dir') . '/tests/fixtures/Import/wallabag-v2-read.json',
            '--importer' => 'v2',
        ]);

        $this->assertStringContainsString('imported', $tester->getDisplay());
        $this->assertStringContainsString('already saved', $tester->getDisplay());
    }

    public function testRunImportCommandWithUserId(): void
    {
        $application = $this->createApplication();
        $userId = $this->getUser('admin')->getId();

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => $userId,
            'filepath' => static::getContainer()->getParameter('kernel.project_dir') . '/tests/fixtures/Import/wallabag-v2-read.json',
            '--useUserId' => true,
            '--importer' => 'v2',
        ]);

        $this->assertStringContainsString('imported', $tester->getDisplay());
        $this->assertStringContainsString('already saved', $tester->getDisplay());
    }

    public function testRunImportCommandWithMoreThanOneBatch(): void
    {
        // Entries are flushed by batches of 20
        $entries = [];
        for ($i = 1; $i <= 21; ++$i) {
            $entries[] = [
                'title' => 'Batch entry ' . $i,
                'url' => 'https://example.org/import-batch/' . $i,
                'content' => '<p>Batch entry ' . $i . '</p>',
                'mimetype' => 'text/html',
                'is_archived' => false,
                'is_starred' => false,
                'tags' => ['import-batch'],
            ];
        }

        $filepath = tempnam(sys_get_temp_dir(), 'wallabag-import');
        file_put_contents($filepath, json_encode($entries));

        $application = $this->createApplication();

        $command = $application->find('wallabag:import');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
            'filepath' => $filepath,
            '--importer' => 'v2',
            '--disableContentUpdate' => true,
        ]);

        unlink($filepath);

        $this->assertStringContainsString('21 imported', $tester->getDisplay());

        $tag = static::getContainer()->get(EntityManagerInterface::class)->getRepository(Tag::class)->findOneByLabel('import-batch');
        $this->assertCount(21, $tag->getEntries());
    }
}
