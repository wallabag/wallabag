<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Entity\AccessToken as BaseAccessToken;
use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\UserBundle\Model\UserInterface;

/**
 * @ORM\Table("oauth2_access_tokens")
 * @ORM\Entity
 * @ORM\AttributeOverrides({
 *     @ORM\AttributeOverride(name="token",
 *         column=@ORM\Column(
 *             name   = "token",
 *             type   = "string",
 *             length = 191
 *         )
 *     ),
 *     @ORM\AttributeOverride(name="scope",
 *         column=@ORM\Column(
 *             name   = "scope",
 *             type   = "string",
 *             length = 191
 *         )
 *     )
 * })
 */
class AccessToken extends BaseAccessToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Client", inversedBy="accessTokens")
     * @ORM\JoinColumn(nullable=false)
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var UserInterface
     */
    protected $user;
}
