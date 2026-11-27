<?php

namespace Wallabag\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\Import\ImportChain;

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
    public function quickstartAction(ImportChain $importChain)
    {
        return $this->render(
            'Static/quickstart.html.twig',
            [
                'imports' => $importChain->getAll(),
            ],
        );
    }
}
