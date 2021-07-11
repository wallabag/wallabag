<?php

namespace Wallabag\CoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Wallabag\CoreBundle\Entity\SiteCredential;
use Wallabag\CoreBundle\Helper\CryptoProxy;
use Wallabag\UserBundle\DataFixtures\UserFixtures;

class SiteCredentialFixtures extends Fixture implements DependentFixtureInterface
{
    private $cryptoProxy;

    public function __construct(CryptoProxy $cryptoProxy)
    {
        $this->cryptoProxy = $cryptoProxy;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager): void
    {
        $credential = new SiteCredential($this->getReference('admin-user'));
        $credential->setHost('.super.com');
        $credential->setUsername($this->cryptoProxy->crypt('.super'));
        $credential->setPassword($this->cryptoProxy->crypt('bar'));

        $manager->persist($credential);

        $credential = new SiteCredential($this->getReference('admin-user'));
        $credential->setHost('paywall.example.com');
        $credential->setUsername($this->cryptoProxy->crypt('paywall.example'));
        $credential->setPassword($this->cryptoProxy->crypt('bar'));

        $manager->persist($credential);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
