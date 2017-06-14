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
     * @param string $host
     * @param int    $userId
     *
     * @return null|array
     */
    public function findOneByHostAndUser($host, $userId)
    {
        $res = $this->createQueryBuilder('s')
            ->select('s.username', 's.password')
            ->where('s.host = :hostname')->setParameter('hostname', $host)
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
