<?php

namespace Wallabag\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Wallabag\Entity\Config;
use Wallabag\Entity\TaggingRule;

class TaggingRuleFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $tr1 = new TaggingRule();
        $tr1->setRule('content matches "spurs"');
        $tr1->setTags(['sport']);
        $tr1->setConfig($this->getReference('admin-config', Config::class));

        $manager->persist($tr1);

        $tr2 = new TaggingRule();
        $tr2->setRule('content matches "basket"');
        $tr2->setTags(['sport']);
        $tr2->setConfig($this->getReference('admin-config', Config::class));

        $manager->persist($tr2);

        $tr3 = new TaggingRule();

        $tr3->setRule('title matches "wallabag"');
        $tr3->setTags(['wallabag']);
        $tr3->setConfig($this->getReference('admin-config', Config::class));

        $manager->persist($tr3);

        $tr4 = new TaggingRule();
        $tr4->setRule('content notmatches "basket"');
        $tr4->setTags(['foot']);
        $tr4->setConfig($this->getReference('admin-config', Config::class));

        $manager->persist($tr4);

        $tr5 = new TaggingRule();
        $tr5->setRule('readingTime <= 5');
        $tr5->setTags(['shortread']);
        $tr5->setConfig($this->getReference('empty-config', Config::class));

        $manager->persist($tr5);

        $tr6 = new TaggingRule();
        $tr6->setRule('readingTime > 5');
        $tr6->setTags(['longread']);
        $tr6->setConfig($this->getReference('empty-config', Config::class));

        $manager->persist($tr6);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ConfigFixtures::class,
        ];
    }
}
