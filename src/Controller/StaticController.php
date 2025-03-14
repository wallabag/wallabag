<?php

namespace Wallabag\Controller;

use Symfony\Component\Routing\Annotation\Route;

class StaticController extends AbstractController
{
    /**
     * @Route("/howto", name="howto", methods={"GET"})
     */
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
     * @Route("/about", name="about", methods={"GET"})
     */
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
     * @Route("/quickstart", name="quickstart", methods={"GET"})
     */
    public function quickstartAction()
    {
        return $this->render(
            'Static/quickstart.html.twig'
        );
    }
}
