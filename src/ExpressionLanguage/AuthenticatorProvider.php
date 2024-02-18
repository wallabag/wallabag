<?php

namespace Wallabag\CoreBundle\ExpressionLanguage;

use GuzzleHttp\ClientInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class AuthenticatorProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @var ClientInterface
     */
    private $guzzle;

    public function __construct(ClientInterface $guzzle)
    {
        $this->guzzle = $guzzle;
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
            function (array $arguments, $uri, array $options = []) {
                return $this->guzzle->get($uri, $options)->getBody();
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
                $useInternalErrors = libxml_use_internal_errors(true);

                $doc = new \DOMDocument();
                $doc->loadHTML((string) $html, \LIBXML_NOCDATA | \LIBXML_NOWARNING | \LIBXML_NOERROR);

                $xpath = new \DOMXPath($doc);
                $domNodeList = $xpath->query($xpathQuery);

                if (0 === $domNodeList->length) {
                    return '';
                }

                $domNode = $domNodeList->item(0);

                libxml_use_internal_errors($useInternalErrors);

                if (null === $domNode || null === $domNode->attributes) {
                    return '';
                }

                return $domNode->attributes->getNamedItem('value')->nodeValue;
            }
        );
    }
}
