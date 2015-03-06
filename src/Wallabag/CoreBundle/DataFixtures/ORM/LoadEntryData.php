<?php

namespace Wallabag\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;

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

        $tag1 = new Tag($this->getReference('bob-user'));
        $tag1->setLabel("foo");
        $tag2 = new Tag($this->getReference('bob-user'));
        $tag2->setLabel("bar");

        $entry3->addTag($tag1);
        $entry3->addTag($tag2);

        $manager->persist($entry3);

        $this->addReference('entry3', $entry3);

        $entry4 = new Entry($this->getReference('admin-user'));
        $entry4->setUrl('http://0.0.0.0');
        $entry4->setTitle('test title entry4');
        $entry4->setContent('This is my content /o/');

        $tag1 = new Tag($this->getReference('admin-user'));
        $tag1->setLabel("foo");
        $tag2 = new Tag($this->getReference('admin-user'));
        $tag2->setLabel("bar");

        $entry4->addTag($tag1);
        $entry4->addTag($tag2);

        $manager->persist($entry4);

        $this->addReference('entry4', $entry4);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 30;
    }
}
