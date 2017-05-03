<?php

namespace Wallabag\CoreBundle\GuzzleSiteAuthenticator;

use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig;
use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfigBuilder;
use Graby\SiteConfig\ConfigBuilder;
use Psr\Log\LoggerInterface;
use Wallabag\CoreBundle\Repository\SiteCredentialRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class GrabySiteConfigBuilder implements SiteConfigBuilder
{
    /**
     * @var ConfigBuilder
     */
    private $grabyConfigBuilder;

    /**
     * @var SiteCredentialRepository
     */
    private $credentialRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Wallabag\UserBundle\Entity\User|null
     */
    private $currentUser;

    /**
     * GrabySiteConfigBuilder constructor.
     *
     * @param ConfigBuilder            $grabyConfigBuilder
     * @param TokenStorage             $token
     * @param SiteCredentialRepository $credentialRepository
     * @param LoggerInterface          $logger
     */
    public function __construct(ConfigBuilder $grabyConfigBuilder, TokenStorage $token, SiteCredentialRepository $credentialRepository, LoggerInterface $logger)
    {
        $this->grabyConfigBuilder = $grabyConfigBuilder;
        $this->credentialRepository = $credentialRepository;
        $this->logger = $logger;

        if ($token->getToken()) {
            $this->currentUser = $token->getToken()->getUser();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForHost($host)
    {
        // required by credentials below
        $host = strtolower($host);
        if (substr($host, 0, 4) == 'www.') {
            $host = substr($host, 4);
        }

        $credentials = null;
        if ($this->currentUser) {
            $credentials = $this->credentialRepository->findOneByHostAndUser($host, $this->currentUser->getId());
        }

        if (null === $credentials) {
            $this->logger->debug('Auth: no credentials available for host.', ['host' => $host]);

            return false;
        }

        $config = $this->grabyConfigBuilder->buildForHost($host);
        $parameters = [
            'host' => $host,
            'requiresLogin' => $config->requires_login ?: false,
            'loginUri' => $config->login_uri ?: null,
            'usernameField' => $config->login_username_field ?: null,
            'passwordField' => $config->login_password_field ?: null,
            'extraFields' => $this->processExtraFields($config->login_extra_fields),
            'notLoggedInXpath' => $config->not_logged_in_xpath ?: null,
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ];

        $config = new SiteConfig($parameters);

        // do not leak password in log
        $parameters['password'] = '**masked**';

        $this->logger->debug('Auth: add parameters.', ['host' => $host, 'parameters' => $parameters]);

        return $config;
    }

    /**
     * Processes login_extra_fields config, transforming an '=' separated array of strings
     * into a key/value array.
     *
     * @param array|mixed $extraFieldsStrings
     *
     * @return array
     */
    protected function processExtraFields($extraFieldsStrings)
    {
        if (!is_array($extraFieldsStrings)) {
            return [];
        }

        $extraFields = [];
        foreach ($extraFieldsStrings as $extraField) {
            if (strpos($extraField, '=') === false) {
                continue;
            }

            list($fieldName, $fieldValue) = explode('=', $extraField, 2);
            $extraFields[$fieldName] = $fieldValue;
        }

        return $extraFields;
    }
}
