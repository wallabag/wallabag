<?php

namespace Wallabag\CoreBundle\GuzzleSiteAuthenticator;

class SiteConfig
{
    private $host;
    private $requiresLogin;
    private $notLoggedInXpath;
    private $loginUri;
    private $usernameField;
    private $passwordField;
    private $extraFields = [];
    private $username;
    private $password;

    public function __construct(array $properties = [])
    {
        foreach ($properties as $name => $value) {
            if (!property_exists($this, $name)) {
                throw new \InvalidArgumentException('Unknown property: "' . $name . '"');
            }

            $this->$name = $value;
        }
    }

    public function getHost()
    {
        return $this->host;
    }

    public function requiresLogin()
    {
        return $this->requiresLogin;
    }

    public function getNotLoggedInXpath()
    {
        return $this->notLoggedInXpath;
    }

    public function getLoginUri()
    {
        return $this->loginUri;
    }

    public function getUsernameField()
    {
        return $this->usernameField;
    }

    public function getPasswordField()
    {
        return $this->passwordField;
    }

    public function getExtraFields()
    {
        return $this->extraFields;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }
}
