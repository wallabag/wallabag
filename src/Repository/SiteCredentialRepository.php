<?php

namespace Wallabag\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Wallabag\Entity\SiteCredential;
use Wallabag\Entity\User;
use Wallabag\Helper\CryptoProxy;

/**
 * SiteCredentialRepository.
 *
 * @method SiteCredential[] findByUser(User $user)
 */
class SiteCredentialRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly CryptoProxy $cryptoProxy,
    ) {
        parent::__construct($registry, SiteCredential::class);
    }

    /**
     * Retrieve one username/password for the given host and userId.
     *
     * @param array $hosts  An array of host to look for
     * @param int   $userId
     *
     * @return array|null
     */
    public function findOneByHostsAndUser($hosts, $userId)
    {
        $res = $this->createQueryBuilder('s')
            ->select('s.username', 's.password')
            ->where('s.host IN (:hosts)')->setParameter('hosts', $hosts)
            ->andWhere('s.user = :userId')->setParameter('userId', $userId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $res) {
            return null;
        }

        // decrypt user & password before returning them
        $res['username'] = $this->cryptoProxy->decrypt($res['username']);
        $res['password'] = $this->cryptoProxy->decrypt($res['password']);

        return $res;
    }
}
