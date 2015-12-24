<?php

namespace Wallabag\ImportBundle\Import;

interface ImportInterface
{
    /**
     * Name of the import.
     *
     * @return string
     */
    public function getName();

    /**
     * Description of the import.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Return the oauth url to authenticate the client.
     *
     * @param string $redirectUri Redirect url in case of error
     * @param string $callbackUri Url when the authentication is complete
     *
     * @return string
     */
    public function oAuthRequest($redirectUri, $callbackUri);

    /**
     * Usually called by the previous callback to authorize the client.
     * Then it return a token that can be used for next requests.
     *
     * @return string
     */
    public function oAuthAuthorize();

    /**
     * Import content using the user token.
     *
     * @param string $accessToken User access token
     */
    public function import($accessToken);
}
