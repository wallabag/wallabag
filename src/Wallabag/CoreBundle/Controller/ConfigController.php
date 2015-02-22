<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\CoreBundle\Entity\User;
use Wallabag\CoreBundle\Form\Type\ConfigType;
use Wallabag\CoreBundle\Form\Type\ChangePasswordType;
use Wallabag\CoreBundle\Form\Type\UserType;
use Wallabag\CoreBundle\Form\Type\NewUserType;

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
        $user = $this->getUser();

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
            $user->setPassword($pwdForm->get('new_password')->getData());
            $em->persist($user);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Password updated'
            );

            return $this->redirect($this->generateUrl('config'));
        }

        // handle changing user information
        $userForm = $this->createForm(new UserType(), $user);
        $userForm->handleRequest($request);

        if ($userForm->isValid()) {
            $em->persist($user);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Information updated'
            );

            return $this->redirect($this->generateUrl('config'));
        }

        // handle adding new user
        $newUser = new User();
        $newUserForm = $this->createForm(new NewUserType(), $newUser);
        $newUserForm->handleRequest($request);

        if ($newUserForm->isValid()) {
            $em->persist($newUser);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                sprintf('User "%s" added', $newUser->getUsername())
            );

            return $this->redirect($this->generateUrl('config'));
        }

        return $this->render('WallabagCoreBundle:Config:index.html.twig', array(
            'configForm' => $configForm->createView(),
            'pwdForm' => $pwdForm->createView(),
            'userForm' => $userForm->createView(),
            'newUserForm' => $newUserForm->createView(),
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
