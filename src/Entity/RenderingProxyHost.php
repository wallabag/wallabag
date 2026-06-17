<?php

namespace Wallabag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Wallabag\Repository\RenderingProxyHostRepository;

/**
 * Rendering proxy host.
 */
#[ORM\Table(name: '`rendering_proxy_host`')]
#[ORM\Entity(repositoryClass: RenderingProxyHostRepository::class)]
class RenderingProxyHost
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
     */
    #[ORM\Column(name: 'host', type: 'string', nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private $host;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Config::class, inversedBy: 'renderingProxyHosts')]
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
     * Set host.
     *
     * @return RenderingProxyHost
     */
    public function setHost(string $host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set config.
     *
     * @return RenderingProxyHost
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
