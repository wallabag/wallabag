<?php

namespace Wallabag\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Wallabag\Entity\Config;
use Wallabag\Entity\TaggingRule;

class TaggingRuleFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $config = $this->getReference('dev-config', Config::class);

        $rules = [
            ['rule' => 'title matches "wallabag"', 'tags' => ['wallabag']],
            ['rule' => 'title matches "pocket"', 'tags' => ['pocket', 'migration']],
            ['rule' => 'title matches "omnivore"', 'tags' => ['omnivore', 'migration']],
            ['rule' => 'title matches "kobo"', 'tags' => ['ereader', 'kobo']],
            ['rule' => 'readingTime <= 5', 'tags' => ['shortread']],
            ['rule' => 'readingTime > 5', 'tags' => ['longread']],
            ['rule' => 'domainName = "nicolas.loeuillet.org"', 'tags' => ['wallabag', 'news']],
        ];

        foreach ($rules as $item) {
            $rule = new TaggingRule();
            $rule->setRule($item['rule']);
            $rule->setTags($item['tags']);
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
