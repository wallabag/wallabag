<?php

namespace Wallabag\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

class StaticController extends AbstractController
{
    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    #[Route(path: '/howto', name: 'howto', methods: ['GET'])]
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

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    #[Route(path: '/about', name: 'about', methods: ['GET'])]
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

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    #[Route(path: '/quickstart', name: 'quickstart', methods: ['GET'])]
    public function quickstartAction()
    {
        return $this->render(
            'Static/quickstart.html.twig'
        );
    }
}
