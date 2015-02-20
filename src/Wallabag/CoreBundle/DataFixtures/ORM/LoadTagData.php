<?php

namespace Wallabag\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wallabag\CoreBundle\Entity\Tag;

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

        $tag2 = new Tag();
        $tag2->setLabel('bar');

        $manager->persist($tag2);

        $tag3 = new Tag();
        $tag3->setLabel('baz');

        $manager->persist($tag3);

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
