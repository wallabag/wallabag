<?php

namespace Wallabag\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wallabag\CoreBundle\Entity\Entry;

class LoadEntryData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $entry1 = new Entry($this->getReference('admin-user'));
        $entry1->setUrl('http://0.0.0.0');
        $entry1->setTitle('test title');
        $entry1->setContent('This is my content /o/');

        $manager->persist($entry1);
        $manager->flush();

        $this->addReference('entry1', $entry1);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 20;
    }
}
