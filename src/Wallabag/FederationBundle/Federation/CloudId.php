<?php

namespace Wallabag\FederationBundle\Federation;

use Wallabag\FederationBundle\Entity\Account;

class CloudId {

    /** @var string */
    private $id;

    /** @var string */
    private $user;

    /** @var string */
    private $remote;

    /**
     * CloudId constructor.
     *
     * @param string $id
     */
    public function __construct($id) {
        $this->id = $id;

        $atPos = strpos($id, '@');
        $user = substr($id, 0, $atPos);
        $remote = substr($id, $atPos + 1);
        if (!empty($user) && !empty($remote)) {
            $this->user = $user;
            $this->remote = $remote;
        }
    }

    /**
     * The full remote cloud id
     *
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    public function getDisplayId() {
        return str_replace('https://', '', str_replace('http://', '', $this->getId()));
    }

    /**
     * The username on the remote server
     *
     * @return string
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * The base address of the remote server
     *
     * @return string
     */
    public function getRemote() {
        return $this->remote;
    }

    /**
     * @param Account $account
     * @param string $domain
     * @return CloudId
     */
    public static function getCloudIdFromAccount(Account $account, $domain = '')
    {
        if ($account->getServer() !== null) {
            return new self($account->getUsername() . '@' . $account->getServer());
        }
        return new self($account->getUsername() . '@' . $domain);
    }
}
