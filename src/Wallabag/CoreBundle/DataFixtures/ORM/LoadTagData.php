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
        $tags = [
            'foo-bar-tag' => 'foo bar', //tag used for EntryControllerTest
            'bar-tag' => 'bar',
            'baz-tag' => 'baz', // tag used for ExportControllerTest
            'foo-tag' => 'foo',
            'bob-tag' => 'bob', // tag used for TagRestControllerTest
        ];

        foreach ($tags as $reference => $label) {
            $tag = new Tag();
            $tag->setLabel($label);

            $manager->persist($tag);

            $this->addReference($reference, $tag);
        }

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
