<?php

namespace Wallabag\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Wallabag\Entity\InternalSetting;

class InternalSettingFixtures extends Fixture
{
    private array $defaultInternalSettings;

    public function __construct(array $defaultInternalSettings)
    {
        $this->defaultInternalSettings = $defaultInternalSettings;
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->defaultInternalSettings as $setting) {
            $newSetting = new InternalSetting();
            $newSetting->setName($setting['name']);
            $newSetting->setValue($setting['value']);
            $newSetting->setSection($setting['section']);
            $manager->persist($newSetting);
        }

        $manager->flush();
    }
}
