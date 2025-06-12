<?php

namespace Tests\Wallabag\Command\Import;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\Entry;

class UrlCommandTest extends WallabagTestCase
{
    private $url = 'https://www.20minutes.fr/sport/football/4158082-20250612-euro-espoirs-su-souffrir-ensemble-decimes-bleuets-retiennent-positif-apres-nul-face-portugal';

    public function testRunUrlCommandWithoutArguments()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments');

        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:import:url');

        $tester = new CommandTester($command);
        $tester->execute([]);
    }

    public function testRunUrlCommandWithWrongUsername()
    {
        $this->expectException(NoResultException::class);

        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:import:url');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'random',
            'url' => $this->url,
        ]);
    }

    public function testRunUrlCommand()
    {
        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:import:url');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
            'url' => $this->url,
        ]);

        $this->assertStringContainsString('successfully imported', $tester->getDisplay());
    }

    public function testRunUrlCommandWithTags()
    {
        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:import:url');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
            'url' => $this->url,
            'tags' => 'sport, football',
        ]);

        $this->assertStringContainsString('successfully imported', $tester->getDisplay());

        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $entry = $em->getRepository(Entry::class)->findByUrlAndUserId($this->url, $this->getLoggedInUserId());
        $this->assertContains('football', $entry->getTagsLabel());
        $this->assertNotContains('basketball', $entry->getTagsLabel());
    }

    public function testRunUrlCommandWithUserId()
    {
        $this->logInAs('admin');

        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:import:url');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => $this->getLoggedInUserId(),
            'url' => $this->url,
            '--useUserId' => true,
        ]);

        $this->assertStringContainsString('successfully imported', $tester->getDisplay());
    }
}
