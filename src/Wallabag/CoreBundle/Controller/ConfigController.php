<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\CoreBundle\Entity\TaggingRule;
use Wallabag\CoreBundle\Form\Type\ConfigType;
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
        $configForm = $this->createForm(ConfigType::class, $config, array('action' => $this->generateUrl('config')));
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
        $pwdForm = $this->createForm(ChangePasswordType::class, null, array('action' => $this->generateUrl('config').'#set4'));
        $pwdForm->handleRequest($request);

        if ($pwdForm->isValid()) {
            $user->setPlainPassword($pwdForm->get('new_password')->getData());
            $userManager->updateUser($user, true);

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Password updated'
            );

            return $this->redirect($this->generateUrl('config').'#set4');
        }

        // handle changing user information
        $userForm = $this->createForm(UserInformationType::class, $user, array(
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

            return $this->redirect($this->generateUrl('config').'#set3');
        }

        // handle rss information
        $rssForm = $this->createForm(RssType::class, $config, array('action' => $this->generateUrl('config').'#set2'));
        $rssForm->handleRequest($request);

        if ($rssForm->isValid()) {
            $em->persist($config);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'RSS information updated'
            );

            return $this->redirect($this->generateUrl('config').'#set2');
        }

        // handle tagging rule
        $taggingRule = new TaggingRule();
        $newTaggingRule = $this->createForm(TaggingRuleType::class, $taggingRule, array('action' => $this->generateUrl('config').'#set5'));
        $newTaggingRule->handleRequest($request);

        if ($newTaggingRule->isValid()) {
            $taggingRule->setConfig($config);
            $em->persist($taggingRule);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Tagging rules updated'
            );

            return $this->redirect($this->generateUrl('config').'#set5');
        }

        // handle adding new user
        $newUser = $userManager->createUser();
        // enable created user by default
        $newUser->setEnabled(true);
        $newUserForm = $this->createForm(NewUserType::class, $newUser, array(
            'validation_groups' => array('Profile'),
            'action' => $this->generateUrl('config').'#set6',
        ));
        $newUserForm->handleRequest($request);

        if ($newUserForm->isValid() && $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $userManager->updateUser($newUser, true);

            $config = new Config($newUser);
            $config->setTheme($this->getParameter('wallabag_core.theme'));
            $config->setItemsPerPage($this->getParameter('wallabag_core.items_on_page'));
            $config->setRssLimit($this->getParameter('wallabag_core.rss_limit'));
            $config->setLanguage($this->getParameter('wallabag_core.language'));

            $em->persist($config);

            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('User "%username%" added', array('%username%' => $newUser->getUsername()))
            );

            return $this->redirect($this->generateUrl('config').'#set6');
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
            'twofactor_auth' => $this->getParameter('twofactor_auth'),
        ));
    }

    /**
     * @param Request $request
     *
     * @Route("/generate-token", name="generate_token")
     *
     * @return RedirectResponse|JsonResponse
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

        $this->get('session')->getFlashBag()->add(
            'notice',
            'RSS token updated'
        );

        return $this->redirect($this->generateUrl('config').'#set2');
    }

    /**
     * Deletes a tagging rule and redirect to the config homepage.
     *
     * @param TaggingRule $rule
     *
     * @Route("/tagging-rule/delete/{id}", requirements={"id" = "\d+"}, name="delete_tagging_rule")
     *
     * @return RedirectResponse
     */
    public function deleteTaggingRuleAction(TaggingRule $rule)
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

        return $this->redirect($this->generateUrl('config').'#set5');
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
