<?php

namespace Wallabag\Consumer;

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
     *
     * @param string $job Content of the message (directly from Redis)
     *
     * @return false
     */
    public function isStopJob($job)
    {
        return false;
    }

    /**
     * This abstract method is only used when we use one queue for multiple job type.
     * We don't do that, so we'll always return true.
     *
     * @param string $job Content of the message (directly from Redis)
     *
     * @return true
     */
    public function isMyJob($job)
    {
        return true;
    }
}
