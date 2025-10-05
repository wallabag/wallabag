<?php

namespace Tests\Wallabag\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;

class GenerateUrlHashesCommandTest extends WallabagTestCase
{
    public function testRunGenerateUrlHashesCommand()
    {
        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:generate-hashed-urls');

        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertStringContainsString('Generating hashed urls for "3" users', $tester->getDisplay());
        $this->assertStringContainsString('Finished generated hashed urls', $tester->getDisplay());
    }

    public function testRunGenerateUrlHashesCommandWithBadUsername()
    {
        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:generate-hashed-urls');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'unknown',
        ]);

        $this->assertStringContainsString('User "unknown" not found', $tester->getDisplay());
    }

    public function testRunGenerateUrlHashesCommandForUser()
    {
        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:generate-hashed-urls');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
        ]);

        $this->assertStringContainsString('Generated hashed urls for user: admin', $tester->getDisplay());
    }

    public function testGenerateUrls()
    {
        $url = 'http://www.lemonde.fr/sport/visuel/2017/05/05/rondelle-prison-blanchissage-comprendre-le-hockey-sur-glace_5122587_3242.html';
        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $this->logInAs('admin');

        $user = $em->getRepository(User::class)->findOneById($this->getLoggedInUserId());

        $entry1 = new Entry($user);
        $entry1->setUrl($url);

        $em->persist($entry1);
        $em->flush();

        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:generate-hashed-urls');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
        ]);

        $this->assertStringContainsString('Generated hashed urls for user: admin', $tester->getDisplay());

        $entry = $em->getRepository(Entry::class)->findOneByUrl($url);

        $this->assertSame($entry->getHashedUrl(), hash('sha1', $url));

        $query = $em->createQuery('DELETE FROM Wallabag\Entity\Entry e WHERE e.url = :url');
        $query->setParameter('url', $url);
        $query->execute();
    }
}
