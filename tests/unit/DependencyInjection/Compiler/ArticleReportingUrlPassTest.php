<?php

namespace Wallabag\Tests\Unit\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wallabag\DependencyInjection\Compiler\ArticleReportingUrlPass;

class ArticleReportingUrlPassTest extends TestCase
{
    private const ENV_NAME = 'WALLABAG_ARTICLE_REPORTING_URL';
    private const DEFAULT_REPORTING_URL = 'mailto:siteconfig@wallabag.org?subject=Wrong%20display%20in%20wallabag';

    private bool $hadEnvValue;
    private bool $hadServerValue;
    private ?string $envValue = null;
    private ?string $serverValue = null;
    private string|false $processEnvValue;

    protected function setUp(): void
    {
        $this->hadEnvValue = \array_key_exists(self::ENV_NAME, $_ENV);
        $this->hadServerValue = \array_key_exists(self::ENV_NAME, $_SERVER);
        $this->envValue = $_ENV[self::ENV_NAME] ?? null;
        $this->serverValue = $_SERVER[self::ENV_NAME] ?? null;
        $this->processEnvValue = getenv(self::ENV_NAME);
    }

    protected function tearDown(): void
    {
        if ($this->hadEnvValue) {
            $_ENV[self::ENV_NAME] = $this->envValue;
        } else {
            unset($_ENV[self::ENV_NAME]);
        }

        if ($this->hadServerValue) {
            $_SERVER[self::ENV_NAME] = $this->serverValue;
        } else {
            unset($_SERVER[self::ENV_NAME]);
        }

        if (false === $this->processEnvValue) {
            putenv(self::ENV_NAME);
        } else {
            putenv(self::ENV_NAME . '=' . $this->processEnvValue);
        }
    }

    /**
     * @dataProvider validArticleReportingUrlProvider
     */
    public function testProcessResolvesArticleReportingUrl(?string $configuredUrl, string $expectedUrl): void
    {
        $container = $this->createContainer($configuredUrl);

        (new ArticleReportingUrlPass())->process($container);

        $reportingUrl = $container->getParameter('wallabag.article_reporting_url');

        $this->assertIsString($reportingUrl);
        $this->assertStringNotContainsString('env_', $reportingUrl);
        $this->assertSame($expectedUrl, $container->getParameterBag()->unescapeValue($reportingUrl));
    }

    public function validArticleReportingUrlProvider(): iterable
    {
        yield 'unset value uses the default' => [null, self::DEFAULT_REPORTING_URL];
        yield 'empty value uses the default' => ['', self::DEFAULT_REPORTING_URL];
        yield 'HTTPS URL' => ['https://support.example.com/issues/new', 'https://support.example.com/issues/new'];
        yield 'HTTPS URL with query and fragment' => [
            'https://support.example.com/issues/new?template=article#report',
            'https://support.example.com/issues/new?template=article#report',
        ];
        yield 'mailto URI' => ['mailto:support@example.com', 'mailto:support@example.com'];
        yield 'mailto URI with query' => [
            'mailto:support@example.com?subject=Article%20problem',
            'mailto:support@example.com?subject=Article%20problem',
        ];
    }

    /**
     * @dataProvider invalidArticleReportingUrlProvider
     */
    public function testProcessRejectsInvalidArticleReportingUrl(string $configuredUrl): void
    {
        $container = $this->createContainer($configuredUrl);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('WALLABAG_ARTICLE_REPORTING_URL must be an absolute HTTPS URL or a mailto URI with one valid recipient.');

        (new ArticleReportingUrlPass())->process($container);
    }

    public function invalidArticleReportingUrlProvider(): iterable
    {
        yield 'whitespace' => [' '];
        yield 'relative URL' => ['/issues/new'];
        yield 'HTTP URL' => ['http://support.example.com/issues/new'];
        yield 'unsupported scheme' => ['javascript:alert(1)'];
        yield 'malformed HTTPS URL' => ['https://'];
        yield 'mailto without recipient' => ['mailto:'];
        yield 'mailto with invalid recipient' => ['mailto:not-an-email'];
    }

    private function createContainer(?string $configuredUrl): ContainerBuilder
    {
        if (null === $configuredUrl) {
            unset($_ENV[self::ENV_NAME], $_SERVER[self::ENV_NAME]);
            putenv(self::ENV_NAME);
        } else {
            $_ENV[self::ENV_NAME] = $configuredUrl;
            $_SERVER[self::ENV_NAME] = $configuredUrl;
            putenv(self::ENV_NAME . '=' . $configuredUrl);
        }

        $container = new ContainerBuilder();
        $container->setParameter('env(WALLABAG_ARTICLE_REPORTING_URL)', '');
        $container->setParameter('wallabag.article_reporting_url', '%env(WALLABAG_ARTICLE_REPORTING_URL)%');

        return $container;
    }
}
