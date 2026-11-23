<?php

namespace Wallabag\MailImport;

class MailMessage
{
    /**
     * @param string[] $recipients
     */
    public function __construct(
        public readonly int $uid,
        public readonly array $recipients,
        public readonly string $rawHeader,
        public readonly string $text,
        public readonly string $html,
    ) {
    }
}
