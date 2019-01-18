<?php

namespace Tests\Wallabag\UserBundle\Mailer;

/**
 * @see https://www.pmg.com/blog/integration-testing-swift-mailer/
 */
final class CountableMemorySpool extends \Swift_MemorySpool implements \Countable
{
    public function count()
    {
        return \count($this->messages);
    }

    public function getMessages()
    {
        return $this->messages;
    }
}
