<?php

namespace Wallabag\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Entity\Comment;

class LoadEntryData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $entry1 = new Entry($this->getReference('admin-user'));
        $entry1->setUrl('http://0.0.0.0');
        $entry1->setTitle('test title entry1');
        $entry1->setContent('This is my content /o/');

        $manager->persist($entry1);

        $this->addReference('entry1', $entry1);

        $entry2 = new Entry($this->getReference('admin-user'));
        $entry2->setUrl('http://0.0.0.0');
        $entry2->setTitle('test title entry2');
        $entry2->setContent('This is my content /o/');

        $manager->persist($entry2);

        $this->addReference('entry2', $entry2);

        $entry3 = new Entry($this->getReference('bob-user'));
        $entry3->setUrl('http://0.0.0.0');
        $entry3->setTitle('test title entry3');
        $entry3->setContent('This is my content /o/');

        $tag1 = new Tag($this->getReference('bob-user'));
        $tag1->setLabel('foo');
        $tag2 = new Tag($this->getReference('bob-user'));
        $tag2->setLabel('bar');

        $entry3->addTag($tag1);
        $entry3->addTag($tag2);

        $manager->persist($entry3);

        $this->addReference('entry3', $entry3);

        $entry4 = new Entry($this->getReference('admin-user'));
        $entry4->setUrl('http://0.0.0.0');
        $entry4->setTitle('test title entry4');
        $entry4->setContent('This is my content /o/');

        $tag1 = new Tag($this->getReference('admin-user'));
        $tag1->setLabel('foo');
        $tag2 = new Tag($this->getReference('admin-user'));
        $tag2->setLabel('bar');

        $entry4->addTag($tag1);
        $entry4->addTag($tag2);

        $manager->persist($entry4);

        $this->addReference('entry4', $entry4);

        $entry5 = new Entry($this->getReference('admin-user'));
        $entry5->setUrl('http://0.0.0.0');
        $entry5->setTitle('test title entry5');
        $entry5->setContent('This is my content /o/');
        $entry5->setStarred(true);

        $manager->persist($entry5);

        $this->addReference('entry5', $entry5);

        $entry6 = new Entry($this->getReference('admin-user'));
        $entry6->setUrl('http://0.0.0.0');
        $entry6->setTitle('test title entry6');
        $entry6->setContent('This is my content /o/');
        $entry6->setArchived(true);

        $manager->persist($entry6);

        $this->addReference('entry6', $entry6);

        $entry7 = new Entry($this->getReference('admin-user'));
        $entry7->setUrl('http://0.0.0.0');
        $entry7->setTitle('test title entry7');
        $entry7->setContent('This is my content /o/ and my comments.
            <br><br><br><br><br><br><br><br><br><br><br><br><br><br>
            <br><br><br><br><br><br><br><br><br><br><br><br><br><br>
            <br><br><br><br><br><br><br><br><br><br><br><br><br><br>
            <br><br><br><br><br><br><br><br><br><br><br><br><br><br>
            <br><br><br><br><br><br><br><br><br><br><br><br><br><br>
            Much content before comments');

        $comment1 = new Comment($this->getReference('admin-user'));
        $comment1->setContent("I'm an admin and I write a comment");

        $entry7->addComment($comment1);
        $comment1->setEntry($entry7);

        $manager->persist($entry7);
        $manager->persist($comment1);

        $this->addReference('entry7', $entry7);

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
