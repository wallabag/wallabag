<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\CoreBundle\Form\Type\ConfigType;

class ConfigController extends Controller
{
    /**
     * @param Request $request
     *
     * @Route("/config", name="config")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $config = $this->getConfig();

        $form = $this->createForm(new ConfigType(), $config);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($config);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Config saved'
            );

            return $this->redirect($this->generateUrl('config'));
        }

        return $this->render('WallabagCoreBundle:Config:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    private function getConfig()
    {
        $config = $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Config')
            ->findOneByUser($this->getUser());

        if (!$config) {
            $config = new Config($this->getUser());
        }

        return $config;
    }
}
