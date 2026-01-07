<?php

namespace Wallabag\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Wallabag\Entity\Tag;

class TagFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tags = [
            'foo-bar-tag' => 'foo bar', // tag used for EntryControllerTest
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
