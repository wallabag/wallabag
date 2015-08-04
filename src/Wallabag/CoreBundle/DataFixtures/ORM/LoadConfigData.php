<?php

namespace Wallabag\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wallabag\CoreBundle\Entity\Config;

class LoadConfigData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $adminConfig = new Config($this->getReference('admin-user'));
        $adminConfig->setTheme('material');
        $adminConfig->setItemsPerPage(30);
        $adminConfig->setLanguage('en_US');

        $manager->persist($adminConfig);

        $this->addReference('admin-config', $adminConfig);

        $bobConfig = new Config($this->getReference('bob-user'));
        $bobConfig->setTheme('default');
        $bobConfig->setItemsPerPage(10);
        $bobConfig->setLanguage('fr_FR');

        $manager->persist($bobConfig);

        $this->addReference('bob-config', $bobConfig);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 20;
    }
}
