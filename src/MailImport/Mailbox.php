<?php

namespace Wallabag\MailImport;

use Webklex\PHPIMAP\Address;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\IMAP;
use Webklex\PHPIMAP\Message;

class Mailbox
{
    private ?Client $client = null;

    private string $folder = 'INBOX';

    public function __construct(
        private readonly MailImportSettings $settings,
    ) {
    }

    /**
     * @return array{config: array<string, mixed>, folder: string}
     */
    public static function parseDsn(string $dsn): array
    {
        $parts = parse_url($dsn);
        $scheme = strtolower($parts['scheme'] ?? '');

        if (!\in_array($scheme, ['imap', 'imaps'], true) || !isset($parts['host'])) {
            throw new \InvalidArgumentException('WALLABAG_MAIL_IMPORT_DSN must look like imaps://user:password@imap.example.org:993/INBOX.');
        }

        parse_str($parts['query'] ?? '', $query);
        $encryption = 'imaps' === $scheme ? 'ssl' : (string) ($query['encryption'] ?? 'none');

        if (!\in_array($encryption, ['ssl', 'starttls', 'none'], true)) {
            throw new \InvalidArgumentException('The encryption of WALLABAG_MAIL_IMPORT_DSN must be "ssl" (imaps://), "starttls" or "none".');
        }

        return [
            'config' => [
                'host' => $parts['host'],
                'port' => $parts['port'] ?? ('ssl' === $encryption ? 993 : 143),
                'protocol' => 'imap',
                'encryption' => 'none' === $encryption ? false : $encryption,
                'validate_cert' => true,
                'username' => rawurldecode($parts['user'] ?? ''),
                'password' => rawurldecode($parts['pass'] ?? ''),
                'authentication' => null,
                'timeout' => 30,
            ],
            'folder' => rawurldecode(trim($parts['path'] ?? '', '/')) ?: 'INBOX',
        ];
    }

    public function open(): void
    {
        $dsn = self::parseDsn($this->settings->getDsn());
        $this->folder = $dsn['folder'];
        $this->client = (new ClientManager())->make($dsn['config']);
        $this->client->connect();
        $this->client->openFolder($this->folder);
    }

    /**
     * @return iterable<MailMessage>
     */
    public function getMessages(int $limit): iterable
    {
        $client = $this->getClient();
        $uids = array_map('intval', $client->getConnection()->search(['UNDELETED'], IMAP::ST_UID)->validatedData());
        sort($uids);

        $query = $client->getFolderByPath($this->folder)->query()->leaveUnread();

        foreach (\array_slice($uids, 0, $limit) as $uid) {
            yield self::createMailMessage($query->getMessageByUid($uid));
        }
    }

    public static function createMailMessage(Message $message): MailMessage
    {
        $recipients = array_map(
            static fn (Address $address) => $address->mail,
            array_merge($message->getTo()->all(), $message->getCc()->all()),
        );

        return new MailMessage((int) $message->getUid(), $recipients, $message->getTextBody(), $message->getHTMLBody());
    }

    public function delete(int $uid): void
    {
        $this->getClient()->getConnection()->store(['\Deleted'], $uid, null, '+', true, IMAP::ST_UID)->validatedData();
    }

    public function close(): void
    {
        if (null === $this->client) {
            return;
        }

        try {
            $this->client->expunge();
        } finally {
            $this->client->disconnect();
            $this->client = null;
        }
    }

    private function getClient(): Client
    {
        if (null === $this->client) {
            throw new \LogicException('The mailbox is not open.');
        }

        return $this->client;
    }
}
