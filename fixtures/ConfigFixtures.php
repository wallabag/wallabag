<?php

namespace Wallabag\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Wallabag\Entity\Config;
use Wallabag\Entity\User;

class ConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference('dev-user', User::class);

        $config = $manager->getRepository(Config::class)->findOneBy(['user' => $user]);

        if (!$config) {
            $config = new Config($user);
            $config->setItemsPerPage(30);
            $config->setReadingSpeed(200);
            $config->setLanguage('en');
            $config->setActionMarkAsRead(0);
            $config->setListMode(0);
            $config->setDisplayThumbnails(true);

            $manager->persist($config);
            $manager->flush();
        }

        $this->addReference('dev-config', $config);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
