<?php

namespace Wallabag\FederationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Account.
 *
 * @ORM\Entity
 * @UniqueEntity(fields={"domain"}).
 * @ORM\Table(name="`instance`")
 */
class Instance {
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="domain", type="string")
     */
    private $domain;

    /**
     * @var float
     *
     * @ORM\Column(name="score", type="float")
     */
    private $score = 0;

    /**
     * @var array
     *
     * @ORM\OneToMany(targetEntity="Wallabag\FederationBundle\Entity\Account", mappedBy="server")
     */
    private $users;

    /**
     * Instance constructor.
     * @param string $domain
     */
    public function __construct($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param float $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * @return array
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param array $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }
}
