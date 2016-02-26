<?php

namespace Wallabag\AnnotationBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wallabag\AnnotationBundle\Entity\Annotation;

class LoadAnnotationData extends AbstractFixture implements OrderedFixtureInterface
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

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 35;
    }
}
