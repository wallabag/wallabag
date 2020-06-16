<?php

namespace Wallabag\CoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Wallabag\CoreBundle\Entity\IgnoreOriginUserRule;
use Wallabag\UserBundle\DataFixtures\UserFixtures;

class IgnoreOriginUserRuleFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager): void
    {
        $rule = new IgnoreOriginUserRule();
        $rule->setRule('host = "example.fr"');
        $rule->setConfig($this->getReference('admin-user')->getConfig());

        $manager->persist($rule);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
