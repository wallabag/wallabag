<?php
namespace Dropbox;

/**
 * A CURLOPT_WRITEFUNCTION that will write HTTP response data to $outStream if
 * it's an HTTP 200 response.  For all other HTTP status codes, it'll save the
 * output in a string, which you can retrieve it via {@link getErrorBody}.
 *
 * @internal
 */
class CurlStreamRelay
{
    var $outStream;
    var $errorData;
    var $isError;

    function __construct($ch, $outStream)
    {
        $this->outStream = $outStream;
        $this->errorData = array();
        $isError = null;
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, array($this, 'writeData'));
    }

    function writeData($ch, $data)
    {
        if ($this->isError === null) {
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $this->isError = ($statusCode !== 200);
        }

        if ($this->isError) {
            $this->errorData[] = $data;
        } else {
            fwrite($this->outStream, $data);
        }

        return strlen($data);
    }

    function getErrorBody()
    {
        return implode($this->errorData);
    }
}

