<?php

namespace Wallabag\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

class StaticController extends AbstractController
{
    #[Route(path: '/howto', name: 'howto', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function howtoAction()
    {
        $addonsUrl = $this->getParameter('addons_url');

        return $this->render(
            'Static/howto.html.twig',
            [
                'addonsUrl' => $addonsUrl,
            ]
        );
    }

    #[Route(path: '/about', name: 'about', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function aboutAction()
    {
        return $this->render(
            'Static/about.html.twig',
            [
                'version' => $this->getParameter('wallabag.version'),
                'paypal_url' => $this->getParameter('wallabag.paypal_url'),
            ]
        );
    }

    #[Route(path: '/quickstart', name: 'quickstart', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function quickstartAction()
    {
        return $this->render(
            'Static/quickstart.html.twig'
        );
    }
}
