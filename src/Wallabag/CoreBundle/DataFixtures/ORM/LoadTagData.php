<?php

namespace Wallabag\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wallabag\CoreBundle\Entity\Tag;

class LoadTagData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $tag1 = new Tag();
        $tag1->setLabel('foo bar');

        $manager->persist($tag1);

        $this->addReference('foo-bar-tag', $tag1);

        $tag2 = new Tag();
        $tag2->setLabel('bar');

        $manager->persist($tag2);

        $this->addReference('bar-tag', $tag2);

        $tag3 = new Tag();
        $tag3->setLabel('baz');

        $manager->persist($tag3);

        $this->addReference('baz-tag', $tag3);

        $tag4 = new Tag();
        $tag4->setLabel('foo');

        $manager->persist($tag4);

        $this->addReference('foo-tag', $tag4);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 25;
    }
}
