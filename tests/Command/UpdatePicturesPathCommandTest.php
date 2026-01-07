<?php

namespace Tests\Wallabag\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\Entry;

class UpdatePicturesPathCommandTest extends WallabagTestCase
{
    public function testRunUpdatePicturesPathCommandWithoutOldURL()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "old-url")');
        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:update-pictures-path');

        $tester = new CommandTester($command);
        $tester->execute([]);
    }

    public function testRunGenerateUrlHashesCommandForUser()
    {
        $application = new Application($this->getTestClient()->getKernel());
        $this->logInAs('admin');

        $url = 'https://wallabag.org/news/20230620-new-release-wallabag-260/';

        $command = $application->find('wallabag:update-pictures-path');

        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl($url);
        $entry->setPreviewPicture('https://old-url.test/mypicture.jpg');
        $entry->setContent('my great article with a picture <img src="https://old-url.test/mypicture.jpg" />');
        $em->persist($entry);
        $em->flush();

        $tester = new CommandTester($command);
        $tester->execute([
            'old-url' => 'https://old-url.test',
        ]);

        $this->assertStringContainsString('Finished updating.', $tester->getDisplay());

        $entry = $em->getRepository(Entry::class)->findOneByUrl($url);
        $this->assertSame($entry->getPreviewPicture(), $client->getContainer()->getParameter('domain_name') . '/mypicture.jpg');

        $query = $em->createQuery('DELETE FROM Wallabag\Entity\Entry e WHERE e.url = :url');
        $query->setParameter('url', $url);
        $query->execute();
    }
}
