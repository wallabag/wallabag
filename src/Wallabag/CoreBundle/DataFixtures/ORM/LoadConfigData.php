<?php

namespace Wallabag\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wallabag\CoreBundle\Entity\Config;

class LoadConfigData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $adminConfig = new Config($this->getReference('admin-user'));

        $adminConfig->setTheme('material');
        $adminConfig->setItemsPerPage(30);
        $adminConfig->setReadingSpeed(1);
        $adminConfig->setLanguage('en');
        $adminConfig->setPocketConsumerKey('xxxxx');
        $adminConfig->setActionMarkAsRead(0);
        $adminConfig->setListMode(0);

        $manager->persist($adminConfig);

        $this->addReference('admin-config', $adminConfig);

        $bobConfig = new Config($this->getReference('bob-user'));
        $bobConfig->setTheme('default');
        $bobConfig->setItemsPerPage(10);
        $bobConfig->setReadingSpeed(1);
        $bobConfig->setLanguage('fr');
        $bobConfig->setPocketConsumerKey(null);
        $bobConfig->setActionMarkAsRead(1);
        $bobConfig->setListMode(1);

        $manager->persist($bobConfig);

        $this->addReference('bob-config', $bobConfig);

        $emptyConfig = new Config($this->getReference('empty-user'));
        $emptyConfig->setTheme('material');
        $emptyConfig->setItemsPerPage(10);
        $emptyConfig->setReadingSpeed(1);
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
    public function getOrder()
    {
        return 20;
    }
}
