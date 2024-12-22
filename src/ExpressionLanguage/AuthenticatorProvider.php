<?php

namespace Wallabag\ExpressionLanguage;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AuthenticatorProvider implements ExpressionFunctionProviderInterface
{
    private HttpClientInterface $requestHtmlFunctionClient;

    public function __construct(HttpClientInterface $requestHtmlFunctionClient)
    {
        $this->requestHtmlFunctionClient = $requestHtmlFunctionClient;
    }

    public function getFunctions(): array
    {
        $result = [
            $this->getRequestHtmlFunction(),
            $this->getXpathFunction(),
            $this->getPregMatchFunction(),
        ];

        return $result;
    }

    private function getRequestHtmlFunction()
    {
        return new ExpressionFunction(
            'request_html',
            function () {
                throw new \Exception('Not supported');
            },
            function (array $arguments, $uri) {
                return $this->requestHtmlFunctionClient->request('GET', $uri)->getContent();
            }
        );
    }

    private function getPregMatchFunction()
    {
        return new ExpressionFunction(
            'preg_match',
            function () {
                throw new \Exception('Not supported');
            },
            function (array $arguments, $pattern, $html) {
                preg_match($pattern, $html, $matches);

                if (2 !== \count($matches)) {
                    return '';
                }

                return $matches[1];
            }
        );
    }

    private function getXpathFunction()
    {
        return new ExpressionFunction(
            'xpath',
            function () {
                throw new \Exception('Not supported');
            },
            function (array $arguments, $xpathQuery, $html) {
                try {
                    $crawler = new Crawler((string) $html);

                    $crawler = $crawler->filterXPath($xpathQuery);
                } catch (\Throwable $e) {
                    return '';
                }

                if (0 === $crawler->count()) {
                    return '';
                }

                return (string) $crawler->first()->attr('value');
            }
        );
    }
}
