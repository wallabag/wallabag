<?php

namespace Wallabag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\RulerZ\Validator\Constraints as RulerZAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Wallabag\Repository\IgnoreOriginInstanceRuleRepository;

/**
 * Ignore Origin rule.
 */
#[ORM\Table(name: '`ignore_origin_instance_rule`')]
#[ORM\Entity(repositoryClass: IgnoreOriginInstanceRuleRepository::class)]
class IgnoreOriginInstanceRule implements IgnoreOriginRuleInterface, RuleInterface
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     *
     * @RulerZAssert\ValidRule(
     *  allowed_variables={"host","_all"},
     *  allowed_operators={"=","~"}
     * )
     */
    #[ORM\Column(name: 'rule', type: 'string', nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
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
