<?php

namespace Wallabag\CoreBundle\Repository;

use Wallabag\CoreBundle\Helper\CryptoProxy;

/**
 * SiteCredentialRepository.
 */
class SiteCredentialRepository extends \Doctrine\ORM\EntityRepository
{
    private $cryptoProxy;

    public function setCrypto(CryptoProxy $cryptoProxy)
    {
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
