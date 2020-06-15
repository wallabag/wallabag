<?php

namespace Tests\Wallabag\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Wallabag\CoreBundle\Command\GenerateUrlHashesCommand;
use Wallabag\CoreBundle\Entity\Entry;

class GenerateUrlHashesCommandTest extends WallabagCoreTestCase
{
    public function testRunGenerateUrlHashesCommand()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new GenerateUrlHashesCommand());

        $command = $application->find('wallabag:generate-hashed-urls');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertStringContainsString('Generating hashed urls for "3" users', $tester->getDisplay());
        $this->assertStringContainsString('Finished generated hashed urls', $tester->getDisplay());
    }

    public function testRunGenerateUrlHashesCommandWithBadUsername()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new GenerateUrlHashesCommand());

        $command = $application->find('wallabag:generate-hashed-urls');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'unknown',
        ]);

        $this->assertStringContainsString('User "unknown" not found', $tester->getDisplay());
    }

    public function testRunGenerateUrlHashesCommandForUser()
    {
        $application = new Application($this->getClient()->getKernel());
        $application->add(new GenerateUrlHashesCommand());

        $command = $application->find('wallabag:generate-hashed-urls');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'admin',
        ]);

        $this->assertStringContainsString('Generated hashed urls for user: admin', $tester->getDisplay());
    }

    public function testGenerateUrls()
    {
        $url = 'http://www.lemonde.fr/sport/visuel/2017/05/05/rondelle-prison-blanchissage-comprendre-le-hockey-sur-glace_5122587_3242.html';
        $client = $this->getClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');

        $this->logInAs('admin');

        $user = $em->getRepository('WallabagUserBundle:User')->findOneById($this->getLoggedInUserId());

        $entry1 = new Entry($user);
        $entry1->setUrl($url);

        $em->persist($entry1);
        $em->flush();

        $application = new Application($this->getClient()->getKernel());
        $application->add(new GenerateUrlHashesCommand());

        $command = $application->find('wallabag:generate-hashed-urls');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'username' => 'admin',
        ]);

        $this->assertStringContainsString('Generated hashed urls for user: admin', $tester->getDisplay());

        $entry = $em->getRepository('WallabagCoreBundle:Entry')->findOneByUrl($url);

        $this->assertSame($entry->getHashedUrl(), hash('sha1', $url));

        $query = $em->createQuery('DELETE FROM Wallabag\CoreBundle\Entity\Entry e WHERE e.url = :url');
        $query->setParameter('url', $url);
        $query->execute();
    }
}
