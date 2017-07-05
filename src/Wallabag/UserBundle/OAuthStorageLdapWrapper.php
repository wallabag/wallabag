<?php

namespace Wallabag\UserBundle;

use FOS\OAuthServerBundle\Storage\OAuthStorage;
use OAuth2\Model\IOAuth2Client;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class OAuthStorageLdapWrapper extends OAuthStorage
{
    private $ldapManager;

    public function setLdapManager($ldap_manager)
    {
        $this->ldapManager = $ldap_manager;
    }

    public function checkUserCredentials(IOAuth2Client $client, $username, $password)
    {
        try {
            $user = $this->userProvider->loadUserByUsername($username);
        } catch (AuthenticationException $e) {
            return false;
        }

        if ($user->isLdapUser()) {
            return $this->checkLdapUserCredentials($user, $password);
        } else {
            return parent::checkUserCredentials($client, $username, $password);
        }
    }

    private function checkLdapUserCredentials($user, $password)
    {
        if ($this->ldapManager->bind($user, $password)) {
            return array(
        'data' => $user,
      );
        } else {
            return false;
        }
    }
}
