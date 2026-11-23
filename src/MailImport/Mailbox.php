<?php

namespace Wallabag\MailImport;

use Webklex\PHPIMAP\Address;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\IMAP;
use Webklex\PHPIMAP\Message;

class Mailbox
{
    public const REJECTED_FOLDER = 'wallabag-rejected';

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
        $this->client = (new ClientManager())->make($dsn['config']);
        $this->client->connect();

        $folder = $this->client->getFolderByPath($dsn['folder'], false, true);

        if (null === $folder) {
            throw new \RuntimeException(\sprintf('The "%s" folder does not exist.', $dsn['folder']));
        }
        $this->folder = $folder->path;

        if (null === $this->client->getFolderByPath(self::REJECTED_FOLDER, false, true)) {
            $this->client->createFolder(self::REJECTED_FOLDER, false);
        }

        $this->client->openFolder($this->folder);
    }

    /**
     * @return string[]
     */
    public function getFolders(): array
    {
        return array_map(static fn ($folder) => $folder->path, $this->getClient()->getFolders(false)->all());
    }

    /**
     * @return int[]
     */
    public function getUids(int $limit): array
    {
        $client = $this->getClient();
        $client->openFolder($this->folder);
        $uids = array_map('intval', $client->getConnection()->search(['UNDELETED'], IMAP::ST_UID)->validatedData());
        sort($uids);

        return \array_slice($uids, 0, $limit);
    }

    public function fetch(int $uid): MailMessage
    {
        $client = $this->getClient();
        $client->openFolder($this->folder);

        return self::createMailMessage($client->getFolderByPath($this->folder)->query()->leaveUnread()->getMessageByUid($uid));
    }

    public static function createMailMessage(Message $message): MailMessage
    {
        $recipients = array_map(
            static fn (Address $address) => $address->mail,
            array_merge($message->getTo()->all(), $message->getCc()->all()),
        );

        return new MailMessage((int) $message->getUid(), $recipients, (string) $message->getHeader()?->raw, $message->getTextBody(), $message->getHTMLBody());
    }

    public function delete(int $uid): void
    {
        $client = $this->getClient();
        $client->openFolder($this->folder);
        $client->getConnection()->store(['\\Deleted'], $uid, null, '+', true, IMAP::ST_UID)->validatedData();
    }

    public function reject(int $uid): void
    {
        $client = $this->getClient();
        $client->openFolder($this->folder);
        $client->getConnection()->copyMessage(self::REJECTED_FOLDER, $uid, null, IMAP::ST_UID)->validatedData();
        $this->delete($uid);
    }

    public function purgeRejected(int $days): void
    {
        $client = $this->getClient();
        $client->openFolder(self::REJECTED_FOLDER);
        $connection = $client->getConnection();

        $uids = $connection->search(['BEFORE', date('d-M-Y', strtotime(\sprintf('-%d days', $days)))], IMAP::ST_UID)->validatedData();

        foreach ($uids as $uid) {
            $connection->store(['\\Deleted'], (int) $uid, null, '+', true, IMAP::ST_UID)->validatedData();
        }

        $client->expunge();
    }

    public function close(): void
    {
        if (null === $this->client) {
            return;
        }

        try {
            $this->client->openFolder($this->folder);
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
