<?php

namespace Wallabag\FederationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\UserBundle\Entity\User;

/**
 * Account.
 *
 * @ORM\Entity(repositoryClass="Wallabag\FederationBundle\Repository\AccountRepository")
 * @UniqueEntity(fields={"username", "server"}).
 * @ORM\Table(name="`account`")
 */
class Account
{
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
     * @ORM\Column(name="username", type="string")
     */
    private $username;

    /**
     * @var Instance
     *
     * @ORM\ManyToOne(targetEntity="Wallabag\FederationBundle\Entity\Instance", inversedBy="users")
     */
    private $server;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="Wallabag\UserBundle\Entity\User", inversedBy="account")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Wallabag\FederationBundle\Entity\Account", mappedBy="following")
     */
    private $followers;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Wallabag\FederationBundle\Entity\Account", inversedBy="followers")
     * @ORM\JoinTable(name="follow",
     *      joinColumns={@ORM\JoinColumn(name="account_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="follow_account_id", referencedColumnName="id")}
     *      )
     */
    private $following;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     *
     * @Assert\File(mimeTypes={ "image/gif", "image/jpeg", "image/svg+xml", "image/webp", "image/png" })
     */
    private $avatar;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     *
     * @Assert\File(mimeTypes={ "image/gif", "image/jpeg", "image/svg+xml", "image/webp", "image/png" })
     */
    private $banner;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * Account constructor.
     */
    public function __construct()
    {
        $this->followers = new ArrayCollection();
        $this->following = new ArrayCollection();
        $this->liked = new ArrayCollection();
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
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return Account
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param string $server
     * @return Account
     */
    public function setServer($server)
    {
        $this->server = $server;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Account
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    /**
     * @param Collection $followers
     * @return Account
     */
    public function setFollowers($followers)
    {
        $this->followers = $followers;
        return $this;
    }

    /**
     * @param Account $account
     * @return Account
     */
    public function addFollower(Account $account)
    {
        $this->followers->add($account);
        return $this;
    }

    /**
     * @return Collection
     */
    public function getFollowing()
    {
        return $this->following;
    }

    /**
     * @param Collection $following
     * @return Account
     */
    public function setFollowing(Collection $following)
    {
        $this->following = $following;
        return $this;
    }

    /**
     * @param Account $account
     * @return Account
     */
    public function addFollowing(Account $account)
    {
        $this->following->add($account);
        return $this;
    }

    /**
     * @return Collection
     */
    public function getLiked()
    {
        return $this->liked;
    }

    /**
     * @param Collection $liked
     * @return Account
     */
    public function setLiked(Collection $liked)
    {
        $this->liked = $liked;
        return $this;
    }

    /**
     * @param Entry $entry
     * @return Account
     */
    public function addLiked(Entry $entry)
    {
        $this->liked->add($entry);
        return $this;
    }

    /**
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param string $avatar
     * @return Account
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
        return $this;
    }

    /**
     * @return string
     */
    public function getBanner()
    {
        return $this->banner;
    }

    /**
     * @param string $banner
     * @return Account
     */
    public function setBanner($banner)
    {
        $this->banner = $banner;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Account
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

}
