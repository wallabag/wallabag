<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\CoreBundle\Form\Type\ConfigType;
use Wallabag\CoreBundle\Form\Type\ChangePasswordType;

class ConfigController extends Controller
{
    /**
     * @param Request $request
     *
     * @Route("/config", name="config")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $config = $this->getConfig();

        // handle basic config detail
        $configForm = $this->createForm(new ConfigType(), $config);
        $configForm->handleRequest($request);

        if ($configForm->isValid()) {

            $em->persist($config);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Config saved'
            );

            return $this->redirect($this->generateUrl('config'));
        }

        // handle changing password
        $pwdForm = $this->createForm(new ChangePasswordType());
        $pwdForm->handleRequest($request);

        if ($pwdForm->isValid()) {
            $user = $this->getUser();
            $user->setPassword($pwdForm->get('new_password')->getData());
            $em->persist($user);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Password updated'
            );

            return $this->redirect($this->generateUrl('config'));
        }

        return $this->render('WallabagCoreBundle:Config:index.html.twig', array(
            'configForm' => $configForm->createView(),
            'pwdForm' => $pwdForm->createView(),
        ));
    }

    /**
     * Retrieve config for the current user.
     * If no config were found, create a new one.
     *
     * @return Wallabag\CoreBundle\Entity\Config
     */
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
