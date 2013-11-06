<?php
namespace Dropbox;

/**
 * Thrown when the the Dropbox server responds with an HTTP status code we didn't expect.
 */
final class Exception_BadResponseCode extends Exception_BadResponse
{
    /** @var int */
    private $statusCode;

    /**
     * @param string $message
     * @param int $statusCode
     *
     * @internal
     */
    function __construct($message, $statusCode)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
    }

    /**
     * The HTTP status code returned by the Dropbox server.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
