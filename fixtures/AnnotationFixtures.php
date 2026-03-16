<?php

namespace Wallabag\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Wallabag\Entity\Annotation;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;

class AnnotationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference('dev-user', User::class);

        $annotation1 = new Annotation($user);
        $annotation1->setEntry($this->getReference('entry-0', Entry::class));
        $annotation1->setText('Very impressive!!');
        $annotation1->setQuote('That is about 2.69 a day!');
        $annotation1->setRanges([[
            'start' => '/p[1]',
            'startOffset' => '173',
            'end' => '/p[1]',
            'endOffset' => '198',
        ]]);

        $manager->persist($annotation1);

        $annotation2 = new Annotation($user);
        $annotation2->setEntry($this->getReference('entry-2', Entry::class));
        $annotation2->setText('Un bel âge ! Bravo !');
        $annotation2->setQuote('J’ai eu 40 ans.');
        $annotation2->setRanges([[
            'start' => '/main[1]/article[1]/ul[1]/li[1]',
            'startOffset' => '0',
            'end' => '/main[1]/article[1]/ul[1]/li[1]',
            'endOffset' => '15',
        ]]);

        $manager->persist($annotation2);

        $annotation3 = new Annotation($user);
        $annotation3->setEntry($this->getReference('entry-5', Entry::class));
        $annotation3->setText('It\'s wallabag, not Wallabag!');
        $annotation3->setQuote('Wallabag');
        $annotation3->setRanges([[
            'start' => '/h2[1]',
            'startOffset' => '0',
            'end' => '/h2[1]',
            'endOffset' => '8',
        ]]);

        $manager->persist($annotation3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            EntryFixtures::class,
        ];
    }
}
