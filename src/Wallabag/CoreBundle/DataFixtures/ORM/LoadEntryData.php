<?php

namespace Wallabag\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wallabag\CoreBundle\Entity\Entry;

class LoadEntryData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $entry1 = new Entry($this->getReference('admin-user'));
        $entry1->setUrl('http://0.0.0.0/entry1');
        $entry1->setReadingTime(11);
        $entry1->setDomainName('domain.io');
        $entry1->setMimetype('text/html');
        $entry1->setTitle('test title entry1');
        $entry1->setContent('This is my content /o/');
        $entry1->setLanguage('en');

        $entry1->addTag($this->getReference('foo-tag'));
        $entry1->addTag($this->getReference('baz-tag'));

        $manager->persist($entry1);

        $this->addReference('entry1', $entry1);

        $entry2 = new Entry($this->getReference('admin-user'));
        $entry2->setUrl('http://0.0.0.0/entry2');
        $entry2->setReadingTime(1);
        $entry2->setDomainName('domain.io');
        $entry2->setMimetype('text/html');
        $entry2->setTitle('test title entry2');
        $entry2->setContent('This is my content /o/');
        $entry2->setOriginUrl('ftp://oneftp.tld');
        $entry2->setLanguage('fr');

        $manager->persist($entry2);

        $this->addReference('entry2', $entry2);

        $entry3 = new Entry($this->getReference('bob-user'));
        $entry3->setUrl('http://0.0.0.0/entry3');
        $entry3->setReadingTime(1);
        $entry3->setDomainName('domain.io');
        $entry3->setMimetype('text/html');
        $entry3->setTitle('test title entry3');
        $entry3->setContent('This is my content /o/');
        $entry3->setLanguage('en');

        $entry3->addTag($this->getReference('foo-tag'));
        $entry3->addTag($this->getReference('bar-tag'));

        $manager->persist($entry3);

        $this->addReference('entry3', $entry3);

        $entry4 = new Entry($this->getReference('admin-user'));
        $entry4->setUrl('http://0.0.0.0/entry4');
        $entry4->setReadingTime(12);
        $entry4->setDomainName('domain.io');
        $entry4->setMimetype('text/html');
        $entry4->setTitle('test title entry4');
        $entry4->setContent('This is my content /o/');
        $entry4->setLanguage('en');

        $entry4->addTag($this->getReference('foo-tag'));
        $entry4->addTag($this->getReference('bar-tag'));

        $manager->persist($entry4);

        $this->addReference('entry4', $entry4);

        $entry5 = new Entry($this->getReference('admin-user'));
        $entry5->setUrl('http://0.0.0.0/entry5');
        $entry5->setReadingTime(12);
        $entry5->setDomainName('domain.io');
        $entry5->setMimetype('text/html');
        $entry5->setTitle('test title entry5');
        $entry5->setContent('This is my content /o/');
        $entry5->setStarred(true);
        $entry5->setLanguage('fr');
        $entry5->setPreviewPicture('http://0.0.0.0/image.jpg');

        $manager->persist($entry5);

        $this->addReference('entry5', $entry5);

        $entry6 = new Entry($this->getReference('admin-user'));
        $entry6->setUrl('http://0.0.0.0/entry6');
        $entry6->setReadingTime(12);
        $entry6->setDomainName('domain.io');
        $entry6->setMimetype('text/html');
        $entry6->setTitle('test title entry6');
        $entry6->setContent('This is my content /o/');
        $entry6->updateArchived(true);
        $entry6->setLanguage('de');
        $entry6->addTag($this->getReference('bar-tag'));

        $manager->persist($entry6);

        $this->addReference('entry6', $entry6);

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
