<?php

namespace Wallabag\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Wallabag\Entity\InternalSetting;

class InternalSettingFixtures extends Fixture
{
    public function __construct(
        private readonly array $defaultInternalSettings,
    ) {
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
