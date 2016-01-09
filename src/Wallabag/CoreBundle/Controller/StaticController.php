<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StaticController extends Controller
{
    /**
     * @Route("/howto", name="howto")
     */
    public function howtoAction()
    {
        return $this->render(
            'WallabagCoreBundle:Static:howto.html.twig',
            array()
        );
    }

    /**
     * @Route("/about", name="about")
     */
    public function aboutAction()
    {
        return $this->render(
            'WallabagCoreBundle:Static:about.html.twig',
            array()
        );
    }

    /**
     * @Route("/quickstart", name="quickstart")
     */
    public function quickstartAction()
    {
        return $this->render(
            'WallabagCoreBundle:Static:quickstart.html.twig',
            array()
        );
    }
}
