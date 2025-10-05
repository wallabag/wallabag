<?php

namespace Wallabag\SiteConfig;

use Graby\SiteConfig\ConfigBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Wallabag\Repository\SiteCredentialRepository;

class GrabySiteConfigBuilder implements SiteConfigBuilder
{
    /**
     * GrabySiteConfigBuilder constructor.
     */
    public function __construct(
        private readonly ConfigBuilder $grabyConfigBuilder,
        private readonly TokenStorageInterface $token,
        private readonly SiteCredentialRepository $credentialRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function buildForHost($host)
    {
        $user = $this->getUser();

        // required by credentials below
        $host = strtolower($host);
        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        if (!$user) {
            $this->logger->debug('Auth: no current user defined.');

            return false;
        }

        $hosts = [$host];
        // will try to see for a host without the first subdomain (fr.example.org & .example.org)
        $split = explode('.', $host);

        if (\count($split) > 1) {
            // remove first subdomain
            array_shift($split);
            $hosts[] = '.' . implode('.', $split);
        }

        $credentials = $this->credentialRepository->findOneByHostsAndUser($hosts, $user->getId());

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
            'httpHeaders' => $config->http_header,
        ];

        $config = new SiteConfig($parameters);

        // do not leak usernames and passwords in log
        $parameters['username'] = '**masked**';
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
        if (!\is_array($extraFieldsStrings)) {
            return [];
        }

        $extraFields = [];
        foreach ($extraFieldsStrings as $extraField) {
            if (!str_contains((string) $extraField, '=')) {
                continue;
            }

            [$fieldName, $fieldValue] = explode('=', (string) $extraField, 2);
            $extraFields[$fieldName] = $fieldValue;
        }

        return $extraFields;
    }

    private function getUser()
    {
        if ($this->token->getToken() && null !== $this->token->getToken()->getUser()) {
            return $this->token->getToken()->getUser();
        }

        return null;
    }
}
