<?php

namespace Wallabag\CoreBundle\Helper\ContentProxy\Authenticator\WebsiteAuthenticator;

interface CredentialsBased
{
    public function setCredentials($username, $password);

    public function getUsername();

    public function getPassword();
}
