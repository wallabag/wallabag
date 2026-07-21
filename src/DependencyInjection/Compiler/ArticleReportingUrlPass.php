<?php

namespace Wallabag\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ArticleReportingUrlPass implements CompilerPassInterface
{
    private const DEFAULT_REPORTING_URL = 'mailto:siteconfig@wallabag.org?subject=Wrong%20display%20in%20wallabag';
    private const INVALID_REPORTING_URL_MESSAGE = 'WALLABAG_ARTICLE_REPORTING_URL must be an absolute HTTPS URL or a mailto URI with one valid recipient.';

    public function process(ContainerBuilder $container): void
    {
        $reportingUrl = $container->getParameterBag()->unescapeValue(
            $container->resolveEnvPlaceholders(
                $container->getParameter('wallabag.article_reporting_url'),
                true
            )
        );

        if ('' === $reportingUrl) {
            $reportingUrl = self::DEFAULT_REPORTING_URL;
        }

        $scheme = strtolower((string) parse_url($reportingUrl, \PHP_URL_SCHEME));
        $isValid = false;

        if ('https' === $scheme) {
            $isValid = false !== filter_var($reportingUrl, \FILTER_VALIDATE_URL)
                && '' !== (string) parse_url($reportingUrl, \PHP_URL_HOST);
        } elseif ('mailto' === $scheme) {
            $recipient = parse_url($reportingUrl, \PHP_URL_PATH);
            $isValid = false !== filter_var($reportingUrl, \FILTER_VALIDATE_URL)
                && \is_string($recipient)
                && false !== filter_var(rawurldecode($recipient), \FILTER_VALIDATE_EMAIL);
        }

        if (!$isValid) {
            throw new \InvalidArgumentException(self::INVALID_REPORTING_URL_MESSAGE);
        }

        $container->setParameter(
            'wallabag.article_reporting_url',
            $container->getParameterBag()->escapeValue($reportingUrl)
        );
    }
}
