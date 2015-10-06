<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\UserBundle\Entity\User;
use Wallabag\CoreBundle\Form\Type\ChangePasswordType;
use Wallabag\CoreBundle\Form\Type\UserInformationType;
use Wallabag\CoreBundle\Form\Type\NewUserType;
use Wallabag\CoreBundle\Form\Type\RssType;
use Wallabag\CoreBundle\Tools\Utils;

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
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $this->getUser();

        // handle basic config detail (this form is defined as a service)
        $configForm = $this->createForm('config', $config);
        $configForm->handleRequest($request);

        if ($configForm->isValid()) {
            $em->persist($config);
            $em->flush();

            // switch active theme
            $activeTheme = $this->get('liip_theme.active_theme');
            $activeTheme->setName($config->getTheme());

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
            $user->setPlainPassword($pwdForm->get('new_password')->getData());
            $userManager->updateUser($user, true);

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Password updated'
            );

            return $this->redirect($this->generateUrl('config'));
        }

        // handle changing user information
        $userForm = $this->createForm(new UserInformationType(), $user, array('validation_groups' => array('Profile')));
        $userForm->handleRequest($request);

        if ($userForm->isValid()) {
            $userManager->updateUser($user, true);

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Information updated'
            );

            return $this->redirect($this->generateUrl('config'));
        }

        // handle rss information
        $rssForm = $this->createForm(new RssType(), $config);
        $rssForm->handleRequest($request);

        if ($rssForm->isValid()) {
            $em->persist($config);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'RSS information updated'
            );

            return $this->redirect($this->generateUrl('config'));
        }

        // handle adding new user
        $newUser = $userManager->createUser();
        // enable created user by default
        $newUser->setEnabled(true);
        $newUserForm = $this->createForm(new NewUserType(), $newUser, array('validation_groups' => array('Profile')));
        $newUserForm->handleRequest($request);

        if ($newUserForm->isValid() && $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $userManager->updateUser($newUser, true);

            $config = new Config($newUser);
            $config->setTheme($this->container->getParameter('theme'));
            $config->setItemsPerPage($this->container->getParameter('items_on_page'));
            $config->setRssLimit($this->container->getParameter('rss_limit'));
            $config->setLanguage($this->container->getParameter('language'));

            $em->persist($config);

            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                sprintf('User "%s" added', $newUser->getUsername())
            );

            return $this->redirect($this->generateUrl('config'));
        }

        return $this->render('WallabagCoreBundle:Config:index.html.twig', array(
            'form' => array(
                'config' => $configForm->createView(),
                'rss' => $rssForm->createView(),
                'pwd' => $pwdForm->createView(),
                'user' => $userForm->createView(),
                'new_user' => $newUserForm->createView(),
            ),
            'rss' => array(
                'username' => $user->getUsername(),
                'token' => $config->getRssToken(),
            ),
        ));
    }

    /**
     * @param Request $request
     *
     * @Route("/generate-token", name="generate_token")
     *
     * @return JsonResponse
     */
    public function generateTokenAction(Request $request)
    {
        $config = $this->getConfig();
        $config->setRssToken(Utils::generateToken());

        $em = $this->getDoctrine()->getManager();
        $em->persist($config);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(array('token' => $config->getRssToken()));
        }

        return $request->headers->get('referer') ? $this->redirect($request->headers->get('referer')) : $this->redirectToRoute('config');
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
