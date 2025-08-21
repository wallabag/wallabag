<?php

namespace Wallabag\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Wallabag\Entity\User;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $userAdmin = new User();
        $userAdmin->setName('Big boss');
        $userAdmin->setEmail('bigboss@wallabag.org');
        $userAdmin->setUsername('admin');
        $userAdmin->setPlainPassword('mypassword');
        $userAdmin->setEnabled(true);
        $userAdmin->addRole('ROLE_SUPER_ADMIN');

        $manager->persist($userAdmin);

        $this->addReference('admin-user', $userAdmin);

        $bobUser = new User();
        $bobUser->setName('Bobby');
        $bobUser->setEmail('bobby@wallabag.org');
        $bobUser->setUsername('bob');
        $bobUser->setPlainPassword('mypassword');
        $bobUser->setEnabled(true);

        $manager->persist($bobUser);

        $this->addReference('bob-user', $bobUser);

        $emptyUser = new User();
        $emptyUser->setName('Empty');
        $emptyUser->setEmail('empty@wallabag.org');
        $emptyUser->setUsername('empty');
        $emptyUser->setPlainPassword('mypassword');
        $emptyUser->setEnabled(true);

        $manager->persist($emptyUser);

        $this->addReference('empty-user', $emptyUser);

        $manager->flush();
    }
}
