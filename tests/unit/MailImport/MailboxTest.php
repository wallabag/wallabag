<?php

namespace Wallabag\Tests\Unit\MailImport;

use PHPUnit\Framework\TestCase;
use Wallabag\MailImport\Mailbox;
use Webklex\PHPIMAP\Message;

class MailboxTest extends TestCase
{
    public function testDsn(): void
    {
        $dsn = Mailbox::parseDsn('imaps://wallabag%40example.org:p%40ss@imap.example.org/Archive%20mails');

        self::assertSame('imap.example.org', $dsn['config']['host']);
        self::assertSame(993, $dsn['config']['port']);
        self::assertSame('ssl', $dsn['config']['encryption']);
        self::assertSame('wallabag@example.org', $dsn['config']['username']);
        self::assertSame('p@ss', $dsn['config']['password']);
        self::assertSame('Archive mails', $dsn['folder']);

        $dsn = Mailbox::parseDsn('imap://user:secret@127.0.0.1?encryption=starttls');

        self::assertSame(143, $dsn['config']['port']);
        self::assertSame('starttls', $dsn['config']['encryption']);
        self::assertSame('INBOX', $dsn['folder']);
    }

    public function testInvalidDsn(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Mailbox::parseDsn('pop3://user:secret@pop.example.org');
    }

    public function testMailMessageFromMultipartEmail(): void
    {
        $raw = implode("\r\n", [
            'From: Someone <someone@example.com>',
            'To: Reader <reader@example.com>',
            'Cc: wallabag+abc123@example.org',
            'Subject: Links',
            'MIME-Version: 1.0',
            'Content-Type: multipart/mixed; boundary="mixed"',
            '',
            '--mixed',
            'Content-Type: multipart/alternative; boundary="alt"',
            '',
            '--alt',
            'Content-Type: text/plain; charset=ISO-8859-1',
            'Content-Transfer-Encoding: quoted-printable',
            '',
            'caf=E9 https://example.org/article?x=3D1&y=3D2',
            '--alt',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            '',
            base64_encode('<a href="https://example.org/article?x=1&amp;y=2">Read</a>'),
            '--alt--',
            '--mixed',
            'Content-Type: text/plain; name="notes.txt"',
            'Content-Disposition: attachment; filename="notes.txt"',
            '',
            'https://example.org/attachment',
            '--mixed--',
            '',
        ]);

        $message = Mailbox::createMailMessage(Message::fromString($raw));

        self::assertSame(['reader@example.com', 'wallabag+abc123@example.org'], $message->recipients);
        self::assertSame('café https://example.org/article?x=1&y=2', trim($message->text));
        self::assertStringContainsString('https://example.org/article?x=1&amp;y=2', $message->html);
        self::assertStringNotContainsString('attachment', $message->text);
    }
}
