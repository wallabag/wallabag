<?php

namespace Wallabag\ImportBundle\Consumer;

use Simpleue\Job\Job;

class RedisEntryConsumer extends AbstractConsumer implements Job
{
    /**
     * Handle one message by one message.
     *
     * @param string $job Content of the message (directly from Redis)
     *
     * @return bool
     */
    public function manage($job)
    {
        return $this->handleMessage($job);
    }

    /**
     * Should tell if the given job will kill the worker.
     * We don't want to stop it :).
     */
    public function isStopJob($job)
    {
        return false;
    }
}
