<?php

namespace Wallabag\Entity;

use Craue\ConfigBundle\Entity\BaseSetting;
use Craue\ConfigBundle\Repository\SettingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * InternalSetting.
 *
 * Re-define setting so we can override length attribute to fix utf8mb4 issue.
 */
#[ORM\Table(name: '`internal_setting`')]
#[ORM\Entity(repositoryClass: SettingRepository::class)]
class InternalSetting extends BaseSetting
{
    /**
     * @var string|null
     */
    #[ORM\Column(name: 'value', type: 'string', nullable: true)]
    protected $value;
}
