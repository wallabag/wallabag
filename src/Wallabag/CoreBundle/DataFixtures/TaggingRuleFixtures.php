<?php

namespace Wallabag\CoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Wallabag\CoreBundle\Entity\TaggingRule;

class TaggingRuleFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager): void
    {
        $tr1 = new TaggingRule();
        $tr1->setRule('content matches "spurs"');
        $tr1->setTags(['sport']);
        $tr1->setConfig($this->getReference('admin-config'));

        $manager->persist($tr1);

        $tr2 = new TaggingRule();
        $tr2->setRule('content matches "basket"');
        $tr2->setTags(['sport']);
        $tr2->setConfig($this->getReference('admin-config'));

        $manager->persist($tr2);

        $tr3 = new TaggingRule();

        $tr3->setRule('title matches "wallabag"');
        $tr3->setTags(['wallabag']);
        $tr3->setConfig($this->getReference('admin-config'));

        $manager->persist($tr3);

        $tr4 = new TaggingRule();
        $tr4->setRule('content notmatches "basket"');
        $tr4->setTags(['foot']);
        $tr4->setConfig($this->getReference('admin-config'));

        $manager->persist($tr4);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            ConfigFixtures::class,
        ];
    }
}
