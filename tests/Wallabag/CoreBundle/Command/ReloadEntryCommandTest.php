<?php

namespace Tests\Wallabag\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Wallabag\CoreBundle\Entity\Entry;

class ReloadEntryCommandTest extends WallabagCoreTestCase
{
    public $url = 'https://www.lemonde.fr/pixels/article/2015/03/28/plongee-dans-l-univers-d-ingress-le-jeu-de-google-aux-frontieres-du-reel_4601155_4408996.html';

    /**
     * @var entry
     */
    public $adminEntry;

    /**
     * @var Entry
     */
    public $bobEntry;

    /**
     * @var Entry
     */
    public $bobParsedEntry;

    /**
     * @var Entry
     */
    public $bobNotParsedEntry;

    protected function setUp(): void
    {
        parent::setUp();

        $userRepository = $this->getTestClient()->getContainer()->get('wallabag_user.user_repository.test');

        $user = $userRepository->findOneByUserName('admin');
        $this->adminEntry = new Entry($user);
        $this->adminEntry->setUrl($this->url);
        $this->adminEntry->setTitle('title foo');
        $this->adminEntry->setContent('');
        $this->getEntityManager()->persist($this->adminEntry);

        $user = $userRepository->findOneByUserName('bob');
        $this->bobEntry = new Entry($user);
        $this->bobEntry->setUrl($this->url);
        $this->bobEntry->setTitle('title foo');
        $this->bobEntry->setContent('');
        $this->getEntityManager()->persist($this->bobEntry);

        $this->bobParsedEntry = new Entry($user);
        $this->bobParsedEntry->setUrl($this->url);
        $this->bobParsedEntry->setTitle('title foo');
        $this->bobParsedEntry->setContent('');
        $this->getEntityManager()->persist($this->bobParsedEntry);

        $this->bobNotParsedEntry = new Entry($user);
        $this->bobNotParsedEntry->setUrl($this->url);
        $this->bobNotParsedEntry->setTitle('title foo');
        $this->bobNotParsedEntry->setContent('');
        $this->bobNotParsedEntry->setNotParsed(true);
        $this->getEntityManager()->persist($this->bobNotParsedEntry);

        $this->getEntityManager()->flush();
    }

    /**
     * @group NetworkCalls
     */
    public function testRunReloadEntryCommand()
    {
        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:entry:reload');
        $tester = new CommandTester($command);
        $tester->execute([], [
            'interactive' => false,
        ]);

        $reloadedEntries = $this->getTestClient()
            ->getContainer()
            ->get('wallabag_core.entry_repository.test')
            ->findById([$this->adminEntry->getId(), $this->bobEntry->getId()]);

        foreach ($reloadedEntries as $reloadedEntry) {
            $this->assertNotEmpty($reloadedEntry->getContent());
        }

        $this->assertStringContainsString('Done', $tester->getDisplay());
    }

    /**
     * @group NetworkCalls
     */
    public function testRunReloadEntryWithUsernameCommand()
    {
        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:entry:reload');
        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
        ], [
            'interactive' => false,
        ]);

        $entryRepository = $this->getTestClient()->getContainer()->get('wallabag_core.entry_repository.test');

        $reloadedAdminEntry = $entryRepository->find($this->adminEntry->getId());
        $this->assertNotEmpty($reloadedAdminEntry->getContent());

        $reloadedBobEntry = $entryRepository->find($this->bobEntry->getId());
        $this->assertEmpty($reloadedBobEntry->getContent());

        $this->assertStringContainsString('Done', $tester->getDisplay());
    }

    public function testRunReloadEntryWithNotParsedOption()
    {
        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:entry:reload');
        $tester = new CommandTester($command);
        $tester->execute([
            '--only-not-parsed' => true,
        ]);

        $entryRepository = $this->getTestClient()->getContainer()->get('wallabag_core.entry_repository.test');

        $reloadedBobParsedEntry = $entryRepository->find($this->bobParsedEntry->getId());
        $this->assertEmpty($reloadedBobParsedEntry->getContent());

        $reloadedBobNotParsedEntry = $entryRepository->find($this->bobNotParsedEntry->getId());
        $this->assertNotEmpty($reloadedBobNotParsedEntry->getContent());

        $this->assertStringContainsString('Done', $tester->getDisplay());
    }

    public function testRunReloadEntryWithoutEntryCommand()
    {
        $application = new Application($this->getTestClient()->getKernel());

        $command = $application->find('wallabag:entry:reload');
        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'empty',
        ], [
            'interactive' => false,
        ]);

        $this->assertStringContainsString('No entry to reload', $tester->getDisplay());
        $this->assertStringNotContainsString('Done', $tester->getDisplay());
    }
}
