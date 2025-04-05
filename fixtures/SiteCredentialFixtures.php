<?php

namespace Wallabag\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Wallabag\Entity\SiteCredential;
use Wallabag\Entity\User;
use Wallabag\Helper\CryptoProxy;

class SiteCredentialFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly CryptoProxy $cryptoProxy,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $credential = new SiteCredential($this->getReference('admin-user', User::class));
        $credential->setHost('.super.com');
        $credential->setUsername($this->cryptoProxy->crypt('.super'));
        $credential->setPassword($this->cryptoProxy->crypt('bar'));

        $manager->persist($credential);

        $credential = new SiteCredential($this->getReference('admin-user', User::class));
        $credential->setHost('paywall.example.com');
        $credential->setUsername($this->cryptoProxy->crypt('paywall.example'));
        $credential->setPassword($this->cryptoProxy->crypt('bar'));

        $manager->persist($credential);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
