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

    public function testLinksAreSavedForTheRecipient(): void
    {
        $this->setToken('admin', 'abc123');

        $mailbox = $this->createMailbox([
            new MailMessage(1, ['wallabag+ABC123@example.org'], "http://0.0.0.0/entry1\n" . self::NEW_URL . "\n" . self::NEW_URL . "\nhttps://example.org/mail-import/over-limit", ''),
        ]);
        $mailbox->expects($this->once())->method('delete')->with(1);
        $mailbox->expects($this->once())->method('close');

        $tester = $this->runCommand($mailbox, 'wallabag@example.org', [], 2);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('1 article(s) saved', $tester->getDisplay());

        $entries = $this->getEntityManager()->getRepository(Entry::class)->findBy(['url' => [self::NEW_URL, 'https://example.org/mail-import/over-limit']]);
        $this->assertCount(1, $entries);
        $this->assertSame('admin', $entries[0]->getUser()->getUsername());
    }

    public function testUnknownAddressesAreDeleted(): void
    {
        $mailbox = $this->createMailbox([
            new MailMessage(1, ['wallabag+unknown@example.org'], self::NEW_URL, ''),
            new MailMessage(2, ['someone@example.org'], self::NEW_URL, ''),
        ]);
        $mailbox->expects($this->exactly(2))->method('delete');

        $this->assertSame(0, $this->runCommand($mailbox, 'wallabag@example.org')->getStatusCode());
        $this->assertNull($this->getEntityManager()->getRepository(Entry::class)->findOneBy(['url' => self::NEW_URL]));
    }

    public function testFailedMessageIsKeptWithoutBlockingTheNextOnes(): void
    {
        $this->setToken('admin', 'abc123');

        $mailbox = $this->createMailbox([
            new MailMessage(1, ['wallabag+abc123@example.org'], 'https://example.org/mail-import/broken', ''),
            new MailMessage(2, ['wallabag+abc123@example.org'], self::NEW_URL, ''),
        ]);
        $mailbox->expects($this->once())->method('delete')->with(2);

        $tester = $this->runCommand($mailbox, 'wallabag@example.org');

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('Unable to import message 1', $tester->getDisplay());
        $this->assertNotNull($this->getEntityManager()->getRepository(Entry::class)->findOneBy(['url' => self::NEW_URL]));
    }

    /**
     * @param MailMessage[] $messages
     */
    private function createMailbox(array $messages): MockObject&Mailbox
    {
        $mailbox = $this->createMock(Mailbox::class);
        $mailbox->expects($this->once())->method('open');
        $mailbox->method('getMessages')->willReturn($messages);

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
