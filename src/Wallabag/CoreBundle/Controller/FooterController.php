<?php

namespace Wallabag\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FooterController extends Controller
{
    /**
     * Display the footer
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $addonsUrl = $this->container->getParameter('addons_url');
        $socialsUrl = $this->container->getParameter('socials_url');
        return $this->render(
            'WallabagCoreBundle::footer.html.twig',
            [
                'addonsUrl' => $addonsUrl,
                'socialsUrl' => $socialsUrl
            ]
        );
    }
}
