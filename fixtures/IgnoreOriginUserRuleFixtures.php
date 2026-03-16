<?php

namespace Wallabag\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Wallabag\Entity\Config;
use Wallabag\Entity\IgnoreOriginUserRule;

class IgnoreOriginUserRuleFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $config = $this->getReference('dev-config', Config::class);

        $rules = [
            'host = "feedproxy.google.com"',
            'host = "l.facebook.com"',
        ];

        foreach ($rules as $ruleExpression) {
            $rule = new IgnoreOriginUserRule();
            $rule->setRule($ruleExpression);
            $rule->setConfig($config);

            $manager->persist($rule);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ConfigFixtures::class,
        ];
    }
}
