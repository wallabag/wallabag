<?php

namespace Wallabag\Tests\Integration\Command;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\Entity\Entry;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class UpdatePicturesPathCommandTest extends WallabagKernelTestCase
{
    public function testRunUpdatePicturesPathCommandWithoutOldURL()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "old-url")');
        $application = $this->createApplication();

        $command = $application->find('wallabag:update-pictures-path');

        $tester = new CommandTester($command);
        $tester->execute([]);
    }

    public function testRunGenerateUrlHashesCommandForUser()
    {
        $application = $this->createApplication();
        $em = $this->getEntityManager();
        $user = $this->getUser('admin');

        $url = 'https://wallabag.org/news/20230620-new-release-wallabag-260/';

        $command = $application->find('wallabag:update-pictures-path');
        $entry = new Entry($user);
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
        $this->assertInstanceOf(Entry::class, $entry);
        $this->assertSame($entry->getPreviewPicture(), static::getContainer()->getParameter('domain_name') . '/mypicture.jpg');

        $query = $em->createQuery('DELETE FROM Wallabag\Entity\Entry e WHERE e.url = :url');
        $query->setParameter('url', $url);
        $query->execute();
    }
}
