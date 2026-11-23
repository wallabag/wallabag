<?php

namespace Wallabag\Tests\Integration\Command\Import;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\Entity\Config;
use Wallabag\Entity\Entry;
use Wallabag\Helper\ContentProxy;
use Wallabag\MailImport\Mailbox;
use Wallabag\MailImport\MailImportSettings;
use Wallabag\MailImport\MailMessage;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class MailCommandTest extends WallabagKernelTestCase
{
    private const NEW_URL = 'https://example.org/mail-import/new';

    protected function tearDown(): void
    {
        $em = $this->getEntityManager();
        $em->createQuery('DELETE FROM ' . Entry::class . ' e WHERE e.url LIKE :url')
            ->setParameter('url', 'https://example.org/mail-import/%')
            ->execute();
        $em->getConnection()->executeStatement('UPDATE ' . $em->getClassMetadata(Config::class)->getTableName() . ' SET mail_import_token = NULL');

        parent::tearDown();
    }

    public function testDisabledImportDoesNothing(): void
    {
        $mailbox = $this->createMock(Mailbox::class);
        $mailbox->expects($this->never())->method('open');

        $this->assertSame(0, $this->runCommand($mailbox, '')->getStatusCode());
    }

    public function testCheckFailsWhenDisabled(): void
    {
        $this->assertSame(1, $this->runCommand($this->createMock(Mailbox::class), '', ['--check' => true])->getStatusCode());
    }

    public function testCheckListsFolders(): void
    {
        $mailbox = $this->createMock(Mailbox::class);
        $mailbox->expects($this->never())->method('open');
        $mailbox->expects($this->once())->method('check')->willReturn(['INBOX', Mailbox::REJECTED_FOLDER]);
        $mailbox->expects($this->never())->method('getUids');

        $tester = $this->runCommand($mailbox, 'wallabag@example.org', ['--check' => true]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString(Mailbox::REJECTED_FOLDER, $tester->getDisplay());
        $this->assertStringContainsString('wallabag+<token>@example.org', $tester->getDisplay());
    }

    public function testInvalidMaxLinks(): void
    {
        $mailbox = $this->createMock(Mailbox::class);
        $mailbox->expects($this->never())->method('open');

        $this->assertSame(1, $this->runCommand($mailbox, 'wallabag@example.org', [], 0)->getStatusCode());
    }

    /**
     * @dataProvider provideInvalidLimits
     */
    public function testInvalidLimit(string $limit): void
    {
        $mailbox = $this->createMock(Mailbox::class);
        $mailbox->expects($this->never())->method('open');

        $this->assertSame(1, $this->runCommand($mailbox, 'wallabag@example.org', ['--limit' => $limit])->getStatusCode());
    }

    public static function provideInvalidLimits(): iterable
    {
        yield ['0'];
        yield ['-1'];
    }

    public function testLinksAreSavedForTheRecipient(): void
    {
        $this->setToken('admin', 'abc123');

        $mailbox = $this->createMailbox([
            1 => new MailMessage(1, ['wallabag+ABC123@example.org'], '', "http://0.0.0.0/entry1\n" . self::NEW_URL . "\n" . self::NEW_URL . "\nhttps://example.org/mail-import/over-limit", ''),
        ]);
        $mailbox->expects($this->once())->method('delete')->with(1);
        $mailbox->expects($this->never())->method('reject');
        $mailbox->expects($this->once())->method('purgeRejected')->with(30);
        $mailbox->expects($this->once())->method('close');

        $tester = $this->runCommand($mailbox, 'wallabag@example.org', [], 2);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('1 article(s) saved', $tester->getDisplay());

        $entries = $this->getEntityManager()->getRepository(Entry::class)->findBy(['url' => [self::NEW_URL, 'https://example.org/mail-import/over-limit']]);
        $this->assertCount(1, $entries);
        $this->assertSame('admin', $entries[0]->getUser()->getUsername());
    }

    public function testRecipientFoundInDeliveryHeaders(): void
    {
        $this->setToken('admin', 'abc123');

        $mailbox = $this->createMailbox([
            1 => new MailMessage(1, ['reader@example.com'], "Delivered-To: wallabag+abc123@example.org\r\n", self::NEW_URL, ''),
        ]);
        $mailbox->expects($this->never())->method('reject');

        $this->runCommand($mailbox, 'wallabag@example.org');

        $this->assertNotNull($this->getEntityManager()->getRepository(Entry::class)->findOneBy(['url' => self::NEW_URL]));
    }

    public function testUnknownAddressesAreRejected(): void
    {
        $mailbox = $this->createMailbox([
            1 => new MailMessage(1, ['wallabag+unknown@example.org'], '', self::NEW_URL, ''),
            2 => new MailMessage(2, ['someone@example.org'], '', self::NEW_URL, ''),
        ]);
        $mailbox->expects($this->exactly(2))->method('reject');
        $mailbox->expects($this->never())->method('delete');

        $tester = $this->runCommand($mailbox, 'wallabag@example.org');

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringNotContainsString('article(s) saved', $tester->getDisplay());
        $this->assertNull($this->getEntityManager()->getRepository(Entry::class)->findOneBy(['url' => self::NEW_URL]));
    }

    public function testFailedMessagesAreKeptWithoutBlockingTheNextOnes(): void
    {
        $this->setToken('admin', 'abc123');

        $mailbox = $this->createMailbox([
            1 => new MailMessage(1, ['wallabag+abc123@example.org'], '', 'https://example.org/mail-import/broken', ''),
            2 => new \RuntimeException('Unable to parse the message'),
            3 => new MailMessage(3, ['wallabag+abc123@example.org'], '', self::NEW_URL, ''),
        ]);
        $mailbox->expects($this->once())->method('delete')->with(3);
        $mailbox->expects($this->never())->method('reject');

        $tester = $this->runCommand($mailbox, 'wallabag@example.org');

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('Unable to import message 1', $tester->getDisplay());
        $this->assertStringContainsString('Unable to import message 2', $tester->getDisplay());
        $this->assertNotNull($this->getEntityManager()->getRepository(Entry::class)->findOneBy(['url' => self::NEW_URL]));
    }

    /**
     * @param array<int, MailMessage|\Throwable> $messages
     */
    private function createMailbox(array $messages): MockObject&Mailbox
    {
        $mailbox = $this->createMock(Mailbox::class);
        $mailbox->expects($this->once())->method('open');
        $mailbox->method('getUids')->willReturn(array_keys($messages));
        $mailbox->method('fetch')->willReturnCallback(static function (int $uid) use ($messages): MailMessage {
            if ($messages[$uid] instanceof \Throwable) {
                throw $messages[$uid];
            }

            return $messages[$uid];
        });

        return $mailbox;
    }

    private function runCommand(Mailbox $mailbox, string $address, array $input = [], int $maxLinks = 10): CommandTester
    {
        $contentProxy = $this->createMock(ContentProxy::class);
        $contentProxy->method('updateEntry')->willReturnCallback(static function (Entry $entry, string $url): void {
            if (str_ends_with($url, '/broken')) {
                throw new \RuntimeException('Website is down');
            }

            $entry->setUrl($url);
            $entry->setTitle('Saved by email');
        });

        $container = static::getContainer();
        $container->set(Mailbox::class, $mailbox);
        $container->set(ContentProxy::class, $contentProxy);
        $container->set(MailImportSettings::class, new MailImportSettings($address, '' === $address ? '' : 'imaps://user:secret@imap.example.org', $maxLinks));

        $tester = new CommandTester($this->createApplication()->find('wallabag:import:mail'));
        $tester->execute($input);

        return $tester;
    }

    private function setToken(string $username, string $token): void
    {
        $this->getUser($username)->getConfig()->setMailImportToken($token);
        $this->getEntityManager()->flush();
    }
}
