<?php

namespace Wallabag\CoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\UserBundle\DataFixtures\UserFixtures;

class ConfigFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager): void
    {
        $adminConfig = new Config($this->getReference('admin-user'));

        $adminConfig->setTheme('material');
        $adminConfig->setItemsPerPage(30);
        $adminConfig->setReadingSpeed(200);
        $adminConfig->setLanguage('en');
        $adminConfig->setPocketConsumerKey('xxxxx');
        $adminConfig->setActionMarkAsRead(0);
        $adminConfig->setListMode(0);

        $manager->persist($adminConfig);

        $this->addReference('admin-config', $adminConfig);

        $bobConfig = new Config($this->getReference('bob-user'));
        $bobConfig->setTheme('default');
        $bobConfig->setItemsPerPage(10);
        $bobConfig->setReadingSpeed(200);
        $bobConfig->setLanguage('fr');
        $bobConfig->setPocketConsumerKey(null);
        $bobConfig->setActionMarkAsRead(1);
        $bobConfig->setListMode(1);

        $manager->persist($bobConfig);

        $this->addReference('bob-config', $bobConfig);

        $emptyConfig = new Config($this->getReference('empty-user'));
        $emptyConfig->setTheme('material');
        $emptyConfig->setItemsPerPage(10);
        $emptyConfig->setReadingSpeed(200);
        $emptyConfig->setLanguage('en');
        $emptyConfig->setPocketConsumerKey(null);
        $emptyConfig->setActionMarkAsRead(0);
        $emptyConfig->setListMode(0);

        $manager->persist($emptyConfig);

        $this->addReference('empty-config', $emptyConfig);

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
