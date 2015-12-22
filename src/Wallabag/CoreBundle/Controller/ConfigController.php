<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\CoreBundle\Entity\TaggingRule;
use Wallabag\CoreBundle\Form\Type\ChangePasswordType;
use Wallabag\CoreBundle\Form\Type\NewUserType;
use Wallabag\CoreBundle\Form\Type\RssType;
use Wallabag\CoreBundle\Form\Type\TaggingRuleType;
use Wallabag\CoreBundle\Form\Type\UserInformationType;
use Wallabag\CoreBundle\Tools\Utils;
use Wallabag\UserBundle\Entity\User;

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
        $configForm = $this->createForm('config', $config, array('action' => $this->generateUrl('config')));
        $configForm->handleRequest($request);

        if ($configForm->isValid()) {
            $em->persist($config);
            $em->flush();

            // switch active theme
            $activeTheme = $this->get('liip_theme.active_theme');
            $activeTheme->setName($config->getTheme());

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Config saved. Some parameters will be considered after disconnection.'
            );

            return $this->redirect($this->generateUrl('config'));
        }

        // handle changing password
        $pwdForm = $this->createForm(new ChangePasswordType(), null, array('action' => $this->generateUrl('config').'#set4'));
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
        $userForm = $this->createForm(new UserInformationType(), $user, array(
            'validation_groups' => array('Profile'),
            'action' => $this->generateUrl('config').'#set3',
        ));
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
        $rssForm = $this->createForm(new RssType(), $config, array('action' => $this->generateUrl('config').'#set2'));
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

        // handle tagging rule
        $taggingRule = new TaggingRule();
        $newTaggingRule = $this->createForm(new TaggingRuleType(), $taggingRule, array('action' => $this->generateUrl('config').'#set5'));
        $newTaggingRule->handleRequest($request);

        if ($newTaggingRule->isValid()) {
            $taggingRule->setConfig($config);
            $em->persist($taggingRule);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Tagging rules updated'
            );

            return $this->redirect($this->generateUrl('config'));
        }

        // handle adding new user
        $newUser = $userManager->createUser();
        // enable created user by default
        $newUser->setEnabled(true);
        $newUserForm = $this->createForm(new NewUserType(), $newUser, array(
            'validation_groups' => array('Profile'),
            'action' => $this->generateUrl('config').'#set5',
        ));
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
                'new_tagging_rule' => $newTaggingRule->createView(),
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
     * Deletes a tagging rule and redirect to the config homepage.
     *
     * @param TaggingRule $rule
     *
     * @Route("/tagging-rule/delete/{id}", requirements={"id" = "\d+"}, name="delete_tagging_rule")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteTaggingRule(TaggingRule $rule)
    {
        if ($this->getUser()->getId() != $rule->getConfig()->getUser()->getId()) {
            throw $this->createAccessDeniedException('You can not access this tagging ryle.');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($rule);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Tagging rule deleted'
        );

        return $this->redirect($this->generateUrl('config'));
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
