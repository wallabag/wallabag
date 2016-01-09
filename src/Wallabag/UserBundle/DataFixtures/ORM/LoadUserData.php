<?php

namespace Wallabag\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wallabag\UserBundle\Entity\User;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
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

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 10;
    }
}
