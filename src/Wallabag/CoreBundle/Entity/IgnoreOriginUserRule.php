<?php

namespace Wallabag\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\RulerZ\Validator\Constraints as RulerZAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ignore Origin rule.
 *
 * @ORM\Entity(repositoryClass="Wallabag\CoreBundle\Repository\IgnoreOriginUserRuleRepository")
 * @ORM\Table(name="`ignore_origin_user_rule`")
 * @ORM\Entity
 */
class IgnoreOriginUserRule implements IgnoreOriginRuleInterface, RuleInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     * @RulerZAssert\ValidRule(
     *  allowed_variables={"host","pattern"},
     *  allowed_operators={"=","~"}
     * )
     * @ORM\Column(name="rule", type="string", nullable=false)
     */
    private $rule;

    /**
     * @ORM\ManyToOne(targetEntity="Wallabag\CoreBundle\Entity\Config", inversedBy="ignoreOriginRules")
     */
    private $config;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set rule.
     *
     * @return IgnoreOriginRuleInterface
     */
    public function setRule(string $rule)
    {
        $this->rule = $rule;

        return $this;
    }

    /**
     * Get rule.
     *
     * @return string
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * Set config.
     *
     * @return IgnoreOriginUserRule
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get config.
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }
}
