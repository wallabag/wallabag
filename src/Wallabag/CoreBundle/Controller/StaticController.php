<?php

namespace Wallabag\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class StaticController extends Controller
{
    /**
     * @Route("/howto", name="howto")
     */
    public function howtoAction()
    {
        $addonsUrl = $this->container->getParameter('addons_url');

        return $this->render(
            '@WallabagCore/themes/common/Static/howto.html.twig',
            [
                'addonsUrl' => $addonsUrl,
            ]
        );
    }

    /**
     * @Route("/about", name="about")
     */
    public function aboutAction()
    {
        return $this->render(
            '@WallabagCore/themes/common/Static/about.html.twig',
            [
                'version' => $this->getParameter('wallabag_core.version'),
                'paypal_url' => $this->getParameter('wallabag_core.paypal_url'),
            ]
        );
    }

    /**
     * @Route("/quickstart", name="quickstart")
     */
    public function quickstartAction()
    {
        return $this->render(
            '@WallabagCore/themes/common/Static/quickstart.html.twig'
        );
    }
}
