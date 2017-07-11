<?php

namespace Wallabag\UserBundle;

use FR3D\LdapBundle\Hydrator\HydratorInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\UserEvent;

class LdapHydrator implements HydratorInterface
{
    private $userManager;
    private $eventDispatcher;
    private $attributesMap;
    private $enabledAttribute;
    private $ldapBaseDn;
    private $ldapAdminFilter;
    private $ldapDriver;

    public function __construct(
      $user_manager,
      $event_dispatcher,
      array $attributes_map,
      $ldap_base_dn,
      $ldap_admin_filter,
      $ldap_driver
    ) {
        $this->userManager = $user_manager;
        $this->eventDispatcher = $event_dispatcher;

        $this->attributesMap = array(
        'setUsername' => $attributes_map[0],
        'setEmail' => $attributes_map[1],
        'setName' => $attributes_map[2],
      );
        $this->enabledAttribute = $attributes_map[3];

        $this->ldapBaseDn = $ldap_base_dn;
        $this->ldapAdminFilter = $ldap_admin_filter;
        $this->ldapDriver = $ldap_driver;
    }

    public function hydrate(array $ldapEntry)
    {
        $user = $this->userManager->findUserBy(array('dn' => $ldapEntry['dn']));

        if (!$user) {
            $user = $this->userManager->createUser();
            $user->setDn($ldapEntry['dn']);
            $user->setPassword('');
            $this->updateUserFields($user, $ldapEntry);

            $event = new UserEvent($user);
            $this->eventDispatcher->dispatch(FOSUserEvents::USER_CREATED, $event);

            $this->userManager->reloadUser($user);
        } else {
            $this->updateUserFields($user, $ldapEntry);
        }

        return $user;
    }

    private function updateUserFields($user, $ldapEntry)
    {
        foreach ($this->attributesMap as $key => $value) {
            if (is_array($ldapEntry[$value])) {
                $ldap_value = $ldapEntry[$value][0];
            } else {
                $ldap_value = $ldapEntry[$value];
            }

            call_user_func([$user, $key], $ldap_value);
        }

        if ($this->enabledAttribute !== null) {
            $user->setEnabled($ldapEntry[$this->enabledAttribute]);
        } else {
            $user->setEnabled(true);
        }

        if ($this->isAdmin($user)) {
            $user->addRole('ROLE_SUPER_ADMIN');
        } else {
            $user->removeRole('ROLE_SUPER_ADMIN');
        }

        $this->userManager->updateUser($user, true);
    }

    private function isAdmin($user)
    {
        if ($this->ldapAdminFilter === null) {
            return false;
        }

        $escaped_username = ldap_escape($user->getUsername(), '', LDAP_ESCAPE_FILTER);
        $filter = sprintf($this->ldapAdminFilter, $escaped_username);
        $entries = $this->ldapDriver->search($this->ldapBaseDn, $filter);

        return $entries['count'] == 1;
    }
}
