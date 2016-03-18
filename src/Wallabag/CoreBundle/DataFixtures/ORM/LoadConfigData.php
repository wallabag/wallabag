<?php

namespace Wallabag\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\CoreBundle\Entity\TaggingRule;

class LoadConfigData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $adminConfig = new Config($this->getReference('admin-user'));
        $taggingRule = new TaggingRule();

        $taggingRule->setConfig($adminConfig);
        $taggingRule->setRule('title matches "wallabag"');
        $taggingRule->setTags(['wallabag']);
        $manager->persist($taggingRule);

        $adminConfig->setTheme('material');
        $adminConfig->setItemsPerPage(30);
        $adminConfig->setReadingSpeed(1);
        $adminConfig->setLanguage('en');

        $manager->persist($adminConfig);

        $this->addReference('admin-config', $adminConfig);

        $bobConfig = new Config($this->getReference('bob-user'));
        $bobConfig->setTheme('default');
        $bobConfig->setItemsPerPage(10);
        $bobConfig->setReadingSpeed(1);
        $bobConfig->setLanguage('fr');

        $manager->persist($bobConfig);

        $this->addReference('bob-config', $bobConfig);

        $emptyConfig = new Config($this->getReference('empty-user'));
        $emptyConfig->setTheme('material');
        $emptyConfig->setItemsPerPage(10);
        $emptyConfig->setReadingSpeed(1);
        $emptyConfig->setLanguage('en');

        $manager->persist($emptyConfig);

        $this->addReference('empty-config', $emptyConfig);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 20;
    }
}
