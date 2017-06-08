<?php

namespace Wallabag\UserBundle\Security;

use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\ParameterBagUtils;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Security;

/**
 * This is a custom authentication failure.
 * It only aims to add a custom error in log so server admin can configure fail2ban to block IP from people who try to login too much.
 *
 * This only changing thing is the logError() addition
 */
class CustomAuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($failureUrl = ParameterBagUtils::getRequestParameterValue($request, $this->options['failure_path_parameter'])) {
            $this->options['failure_path'] = $failureUrl;
        }

        if (null === $this->options['failure_path']) {
            $this->options['failure_path'] = $this->options['login_path'];
        }

        if ($this->options['failure_forward']) {
            $this->logger->debug('Authentication failure, forward triggered.', ['failure_path' => $this->options['failure_path']]);

            $this->logError($request);

            $subRequest = $this->httpUtils->createRequest($request, $this->options['failure_path']);
            $subRequest->attributes->set(Security::AUTHENTICATION_ERROR, $exception);

            return $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

        $this->logger->debug('Authentication failure, redirect triggered.', ['failure_path' => $this->options['failure_path']]);

        $this->logError($request);

        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);

        return $this->httpUtils->createRedirectResponse($request, $this->options['failure_path']);
    }

    /**
     * Log error information about fialure
     *
     * @param  Request $request
     */
    private function logError(Request $request)
    {
        $this->logger->error('Authentication failure for user "'.$request->request->get('_username').'", from IP "'.$request->getClientIp().'", with UA: "'.$request->server->get('HTTP_USER_AGENT').'".');
    }
}
