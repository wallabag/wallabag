<?php

namespace WallabagBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StaticController extends Controller
{
    /**
     * @Route("/about", name="about")
     */
    public function aboutAction()
    {
        return $this->render(
            'WallabagBundle:Static:about.html.twig',
            array()
        );
    }
}
