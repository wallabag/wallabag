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
        $entry1->setTitle('test title entry1');
        $entry1->setContent('This is my content /o/');

        $manager->persist($entry1);

        $this->addReference('entry1', $entry1);

        $entry2 = new Entry($this->getReference('admin-user'));
        $entry2->setUrl('http://0.0.0.0');
        $entry2->setTitle('test title entry2');
        $entry2->setContent('This is my content /o/');

        $manager->persist($entry2);

        $this->addReference('entry2', $entry2);

        $entry3 = new Entry($this->getReference('bob-user'));
        $entry3->setUrl('http://0.0.0.0');
        $entry3->setTitle('test title entry3');
        $entry3->setContent('This is my content /o/');

        $manager->persist($entry3);

        $this->addReference('entry3', $entry3);

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
