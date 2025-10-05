<?php

namespace Wallabag\SiteConfig;

/**
 * Authentication configuration for a site.
 */
class SiteConfig
{
    /**
     * The site's host name.
     *
     * @var string
     */
    protected $host;

    /**
     * If the site requires a loogin or not.
     *
     * @var bool
     */
    protected $requiresLogin;

    /**
     * XPath query used to check if the user was logged in or not.
     *
     * @var string
     */
    protected $notLoggedInXpath;

    /**
     * URI login data must be sent to.
     *
     * @var string
     */
    protected $loginUri;

    /**
     * Name of the username field.
     *
     * @var string
     */
    protected $usernameField;

    /**
     * Name of the password field.
     *
     * @var string
     */
    protected $passwordField;

    /**
     * Associative array of extra fields to send with the form.
     *
     * @var array
     */
    protected $extraFields = [];

    /**
     * Username to use for login.
     *
     * @var string
     */
    protected $username;

    /**
     * Password to use for login.
     *
     * @var string
     */
    protected $password;

    /**
     * Associative array of HTTP headers to send with the form.
     *
     * @var array
     */
    protected $httpHeaders = [];

    /**
     * SiteConfig constructor. Sets the properties by name given a hash.
     *
     * @throws \InvalidArgumentException if a property doesn't exist
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $propertyName => $propertyValue) {
            if (!property_exists($this, $propertyName)) {
                throw new \InvalidArgumentException('Unknown property: "' . $propertyName . '"');
            }

            $this->$propertyName = $propertyValue;
        }
    }

    /**
     * @return bool
     */
    public function requiresLogin()
    {
        return $this->requiresLogin;
    }

    /**
     * @param bool $requiresLogin
     *
     * @return SiteConfig
     */
    public function setRequiresLogin($requiresLogin)
    {
        $this->requiresLogin = $requiresLogin;

        return $this;
    }

    /**
     * @return string
     */
    public function getNotLoggedInXpath()
    {
        return $this->notLoggedInXpath;
    }

    /**
     * @param string $notLoggedInXpath
     *
     * @return SiteConfig
     */
    public function setNotLoggedInXpath($notLoggedInXpath)
    {
        $this->notLoggedInXpath = $notLoggedInXpath;

        return $this;
    }

    /**
     * @return string
     */
    public function getLoginUri()
    {
        return $this->loginUri;
    }

    /**
     * @param string $loginUri
     *
     * @return SiteConfig
     */
    public function setLoginUri($loginUri)
    {
        $this->loginUri = $loginUri;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsernameField()
    {
        return $this->usernameField;
    }

    /**
     * @param string $usernameField
     *
     * @return SiteConfig
     */
    public function setUsernameField($usernameField)
    {
        $this->usernameField = $usernameField;

        return $this;
    }

    /**
     * @return string
     */
    public function getPasswordField()
    {
        return $this->passwordField;
    }

    /**
     * @param string $passwordField
     *
     * @return SiteConfig
     */
    public function setPasswordField($passwordField)
    {
        $this->passwordField = $passwordField;

        return $this;
    }

    /**
     * @return array
     */
    public function getExtraFields()
    {
        return $this->extraFields;
    }

    /**
     * @param array $extraFields
     *
     * @return SiteConfig
     */
    public function setExtraFields($extraFields)
    {
        $this->extraFields = $extraFields;

        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     *
     * @return SiteConfig
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return SiteConfig
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return SiteConfig
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function getHttpHeaders(): array
    {
        return $this->httpHeaders;
    }

    public function setHttpHeaders(array $httpHeaders): self
    {
        $this->httpHeaders = $httpHeaders;

        return $this;
    }
}
