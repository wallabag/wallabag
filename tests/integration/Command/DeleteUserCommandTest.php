<?php

namespace Wallabag\Tests\Integration\Command;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\Entity\Config;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class DeleteUserCommandTest extends WallabagKernelTestCase
{
    public function testRunDeleteUserCommandWithoutUsername(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments');

        $application = $this->createApplication();

        $command = $application->find('wallabag:user:delete');

        $tester = new CommandTester($command);
        $tester->execute([]);
    }

    public function testRunDeleteUserCommandWithBadUsername(): void
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:user:delete');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'unknown',
        ], [
            'interactive' => false,
        ]);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('User "unknown" not found', $tester->getDisplay());
    }

    public function testRunDeleteUserCommandNonInteractive(): void
    {
        [$userId, $entryId] = $this->createUserWithEntry('wallace');

        $application = $this->createApplication();

        $command = $application->find('wallabag:user:delete');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'wallace',
        ], [
            'interactive' => false,
        ]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('User "wallace" deleted.', $tester->getDisplay());

        $em = $this->getEntityManager();
        $em->clear();

        $this->assertNull($em->getRepository(User::class)->find($userId));
        $this->assertNull($em->getRepository(Entry::class)->find($entryId));
        $this->assertNotNull($em->getRepository(User::class)->findOneBy(['username' => 'admin']));
    }

    public function testRunDeleteUserCommandConfirmed(): void
    {
        [$userId] = $this->createUserWithEntry('wallace');

        $application = $this->createApplication();

        $command = $application->find('wallabag:user:delete');

        $tester = new CommandTester($command);
        $tester->setInputs(['yes']);
        $tester->execute([
            'username' => 'wallace',
        ]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('Are you sure you want to delete the user "wallace"', $tester->getDisplay());
        $this->assertStringContainsString('User "wallace" deleted.', $tester->getDisplay());

        $em = $this->getEntityManager();
        $em->clear();

        $this->assertNull($em->getRepository(User::class)->find($userId));
    }

    public function testRunDeleteUserCommandNotConfirmed(): void
    {
        [$userId, $entryId] = $this->createUserWithEntry('wallace');

        $application = $this->createApplication();

        $command = $application->find('wallabag:user:delete');

        $tester = new CommandTester($command);
        $tester->setInputs(['no']);
        $tester->execute([
            'username' => 'wallace',
        ]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('The user has not been deleted.', $tester->getDisplay());

        $em = $this->getEntityManager();
        $em->clear();

        $this->assertNotNull($em->getRepository(User::class)->find($userId));
        $this->assertNotNull($em->getRepository(Entry::class)->find($entryId));
    }

    /**
     * @return array{int, int} The ids of the created user and entry
     */
    private function createUserWithEntry(string $username): array
    {
        $em = $this->getEntityManager();

        $user = new User();
        $user->setName('Wallace');
        $user->setEmail($username . '@wallabag.org');
        $user->setUsername($username);
        $user->setPlainPassword($username);
        $user->setEnabled(true);
        $em->persist($user);

        $config = new Config($user);
        $config->setItemsPerPage(30);
        $config->setReadingSpeed(200);
        $config->setLanguage('en');
        $em->persist($config);

        $entry = new Entry($user);
        $entry->setUrl('https://www.example.org/article');
        $entry->setTitle('An article');
        $entry->setContent('');
        $em->persist($entry);

        $em->flush();
        $em->clear();

        return [$user->getId(), $entry->getId()];
    }
}
