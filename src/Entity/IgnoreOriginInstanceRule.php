<?php

namespace Wallabag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\RulerZ\Validator\Constraints as RulerZAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ignore Origin rule.
 *
 * @ORM\Entity(repositoryClass="Wallabag\Repository\IgnoreOriginInstanceRuleRepository")
 * @ORM\Table(name="`ignore_origin_instance_rule`")
 */
class IgnoreOriginInstanceRule implements IgnoreOriginRuleInterface, RuleInterface
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
     *  allowed_variables={"host","_all"},
     *  allowed_operators={"=","~"}
     * )
     * @ORM\Column(name="rule", type="string", nullable=false)
     */
    private $rule;

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
}
