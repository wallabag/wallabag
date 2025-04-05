<?php

namespace Wallabag\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\XmlRoot;
use Symfony\Bridge\RulerZ\Validator\Constraints as RulerZAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Wallabag\Repository\TaggingRuleRepository;

/**
 * Tagging rule.
 */
#[ORM\Table(name: '`tagging_rule`')]
#[ORM\Entity(repositoryClass: TaggingRuleRepository::class)]
#[XmlRoot('tagging_rule')]
class TaggingRule implements RuleInterface
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
     *  allowed_variables={"title", "url", "isArchived", "isStarred", "content", "language", "mimetype", "readingTime", "domainName"},
     *  allowed_operators={">", "<", ">=", "<=", "=", "is", "!=", "and", "not", "or", "matches", "notmatches"}
     * )
     */
    #[ORM\Column(name: 'rule', type: 'string', nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['export_tagging_rule'])]
    private $rule;

    /**
     * @var array<string>
     */
    #[ORM\Column(name: 'tags', type: 'simple_array', nullable: false)]
    #[Assert\NotBlank]
    #[Groups(['export_tagging_rule'])]
    private $tags = [];

    #[ORM\ManyToOne(targetEntity: Config::class, inversedBy: 'taggingRules')]
    #[Exclude]
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
     * @param string $rule
     *
     * @return TaggingRule
     */
    public function setRule($rule)
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
     * Set tags.
     *
     * @param array <string> $tags
     *
     * @return TaggingRule
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Get tags.
     *
     * @return array<string>
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set config.
     *
     * @return TaggingRule
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
