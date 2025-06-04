<?php

namespace Wallabag\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Wallabag\Entity\EntryDeletion;
use Wallabag\Entity\User;

class EntryDeletionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $adminUser = $this->getReference('admin-user', User::class);
        $bobUser = $this->getReference('bob-user', User::class);

        $deletions = [
            [
                'user' => $adminUser,
                'entry_id' => 1004,
                'deleted_at' => new \DateTime('-4 day'),
            ],
            [
                'user' => $adminUser,
                'entry_id' => 1001,
                'deleted_at' => new \DateTime('-1 day'),
            ],
            [
                'user' => $bobUser,
                'entry_id' => 1003,
                'deleted_at' => new \DateTime('-3 days'),
            ],
        ];

        foreach ($deletions as $deletionData) {
            $deletion = new EntryDeletion($deletionData['user'], $deletionData['entry_id']);
            $deletion->setDeletedAt($deletionData['deleted_at']);

            $manager->persist($deletion);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            EntryFixtures::class,
        ];
    }
}
