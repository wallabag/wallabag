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
        $comment1->setTitle('titre');
        $comment1->setText('This is my comment /o/');
        $comment1->setUpdated(new \DateTime());
        $comment1->setQuote('un texte cité');
        //$comment1->setRanges(json_encode([{"start":"","startOffset":24,"end":"","endOffset":31}]));

        $manager->persist($comment1);

        $this->addReference('comment1', $comment1);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 30;
    }

}
