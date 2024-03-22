<?php

namespace Wallabag\Entity;

use Craue\ConfigBundle\Entity\BaseSetting;
use Doctrine\ORM\Mapping as ORM;

/**
 * InternalSetting.
 *
 * Re-define setting so we can override length attribute to fix utf8mb4 issue.
 *
 * @ORM\Entity(repositoryClass="Craue\ConfigBundle\Repository\SettingRepository")
 * @ORM\Table(name="`internal_setting`")
 */
class InternalSetting extends BaseSetting
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="value", type="string", nullable=true)
     */
    protected $value;
}
