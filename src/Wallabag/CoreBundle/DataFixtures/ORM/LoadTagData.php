<?php

namespace Wallabag\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Entity\TagsEntries;

class LoadTagData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $tag1 = new Tag();
        $tag1->setLabel('foo');

        $manager->persist($tag1);

        $this->addReference('tag1', $tag1);

        $tagsEntries1 = new TagsEntries();
        $tagsEntries1->setEntryId($this->getReference('entry1'));
        $manager->persist($tagsEntries1);

        $tag2 = new Tag();
        $tag2->setLabel('bar');

        $manager->persist($tag2);

        $this->addReference('tag2', $tag2);

        $tagsEntries2 = new TagsEntries();
        $tagsEntries2->setEntryId($this->getReference('entry2'));
        $manager->persist($tagsEntries2);

        $tag3 = new Tag();
        $tag3->setLabel('baz');

        $manager->persist($tag3);

        $this->addReference('tag3', $tag3);

        $tagsEntries3 = new TagsEntries();
        $tagsEntries3->setEntryId($this->getReference('entry2'));
        $manager->persist($tagsEntries3);

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
