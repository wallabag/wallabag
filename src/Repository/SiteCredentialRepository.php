<?php

namespace App\Repository;

use App\Entity\SiteCredential;
use App\Helper\CryptoProxy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * SiteCredentialRepository.
 *
 * @method SiteCredential[] findByUser(int $userId)
 */
class SiteCredentialRepository extends ServiceEntityRepository
{
    private $cryptoProxy;

    public function __construct(ManagerRegistry $registry, CryptoProxy $cryptoProxy)
    {
        parent::__construct($registry, SiteCredential::class);

        $this->cryptoProxy = $cryptoProxy;
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
            return;
        }

        // decrypt user & password before returning them
        $res['username'] = $this->cryptoProxy->decrypt($res['username']);
        $res['password'] = $this->cryptoProxy->decrypt($res['password']);

        return $res;
    }
}
