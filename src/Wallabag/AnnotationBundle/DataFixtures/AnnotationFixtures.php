<?php

namespace Wallabag\AnnotationBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Wallabag\AnnotationBundle\Entity\Annotation;
use Wallabag\CoreBundle\DataFixtures\EntryFixtures;
use Wallabag\UserBundle\DataFixtures\UserFixtures;

class AnnotationFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $annotation1 = new Annotation($this->getReference('admin-user'));
        $annotation1->setEntry($this->getReference('entry1'));
        $annotation1->setText('This is my annotation /o/');
        $annotation1->setQuote('content');

        $manager->persist($annotation1);

        $this->addReference('annotation1', $annotation1);

        $annotation2 = new Annotation($this->getReference('admin-user'));
        $annotation2->setEntry($this->getReference('entry2'));
        $annotation2->setText('This is my 2nd annotation /o/');
        $annotation2->setQuote('content');

        $manager->persist($annotation2);

        $this->addReference('annotation2', $annotation2);

        $annotation3 = new Annotation($this->getReference('bob-user'));
        $annotation3->setEntry($this->getReference('entry3'));
        $annotation3->setText('This is my first annotation !');
        $annotation3->setQuote('content');

        $manager->persist($annotation3);

        $this->addReference('annotation3', $annotation3);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            EntryFixtures::class,
            UserFixtures::class,
        ];
    }
}
