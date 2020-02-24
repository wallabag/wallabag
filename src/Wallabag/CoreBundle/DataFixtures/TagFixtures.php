<?php

namespace Wallabag\CoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Wallabag\CoreBundle\Entity\Tag;

class TagFixtures extends Fixture
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
}
