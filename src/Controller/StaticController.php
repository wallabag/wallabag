<?php

namespace Wallabag\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StaticController extends AbstractController
{
    public function __construct(
        private readonly array $addonsUrl,
        private readonly string $version,
        private readonly string $paypalUrl,
    ) {
    }

    #[Route(path: '/howto', name: 'howto', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function howtoAction()
    {
        return $this->render(
            'Static/howto.html.twig',
            [
                'addonsUrl' => $this->addonsUrl,
            ],
        );
    }

    #[Route(path: '/about', name: 'about', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function aboutAction()
    {
        return $this->render(
            'Static/about.html.twig',
            [
                'version' => $this->version,
                'paypal_url' => $this->paypalUrl,
            ],
        );
    }

    #[Route(path: '/quickstart', name: 'quickstart', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function quickstartAction()
    {
        return $this->render(
            'Static/quickstart.html.twig',
        );
    }

    /**
     * OpenSearch description, used by browsers to add wallabag as a search engine.
     *
     * @see https://github.com/dewitt/opensearch/blob/master/opensearch-1-1-draft-6.md
     */
    #[Route(path: '/opensearch.xml', name: 'opensearch', methods: ['GET'], defaults: ['_format' => 'xml'])]
    #[IsGranted('PUBLIC_ACCESS')]
    public function openSearchAction()
    {
        return $this->render(
            'Static/opensearch.xml.twig',
            [],
            new Response('', 200, ['Content-Type' => 'application/opensearchdescription+xml']),
        );
    }
}
