<?php

namespace Wallabag\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wallabag\CommentBundle\Entity\Comment;

class LoadCommentData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $comment1 = new Comment($this->getReference('admin-user'));
        $comment1->setEntry($this->getReference('entry1'));
        $comment1->setText('This is my comment /o/');
        $comment1->setQuote('content');

        $manager->persist($comment1);

        $this->addReference('comment1', $comment1);

        $comment2 = new Comment($this->getReference('admin-user'));
        $comment2->setEntry($this->getReference('entry2'));
        $comment2->setText('This is my 2nd comment /o/');
        $comment2->setQuote('content');

        $manager->persist($comment2);

        $this->addReference('comment2', $comment2);

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
