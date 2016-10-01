<?php

namespace Wallabag\CoreBundle\Controller;

use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
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
        $configForm = $this->createForm(ConfigType::class, $config, ['action' => $this->generateUrl('config')]);
        $configForm->handleRequest($request);

        if ($configForm->isValid()) {
            $em->persist($config);
            $em->flush();

            // switch active theme
            $activeTheme = $this->get('liip_theme.active_theme');
            $activeTheme->setName($config->getTheme());

            $this->get('session')->getFlashBag()->add(
                'notice',
                'flashes.config.notice.config_saved'
            );

            return $this->redirect($this->generateUrl('config'));
        }

        // handle changing password
        $pwdForm = $this->createForm(ChangePasswordType::class, null, ['action' => $this->generateUrl('config').'#set4']);
        $pwdForm->handleRequest($request);

        if ($pwdForm->isValid()) {
            if ($this->get('craue_config')->get('demo_mode_enabled') && $this->get('craue_config')->get('demo_mode_username') === $user->getUsername()) {
                $message = 'flashes.config.notice.password_not_updated_demo';
            } else {
                $message = 'flashes.config.notice.password_updated';

                $user->setPlainPassword($pwdForm->get('new_password')->getData());
                $userManager->updateUser($user, true);
            }

            $this->get('session')->getFlashBag()->add('notice', $message);

            return $this->redirect($this->generateUrl('config').'#set4');
        }

        // handle changing user information
        $userForm = $this->createForm(UserInformationType::class, $user, [
            'validation_groups' => ['Profile'],
            'action' => $this->generateUrl('config').'#set3',
        ]);
        $userForm->handleRequest($request);

        if ($userForm->isValid()) {
            $userManager->updateUser($user, true);

            $this->get('session')->getFlashBag()->add(
                'notice',
                'flashes.config.notice.user_updated'
            );

            return $this->redirect($this->generateUrl('config').'#set3');
        }

        // handle rss information
        $rssForm = $this->createForm(RssType::class, $config, ['action' => $this->generateUrl('config').'#set2']);
        $rssForm->handleRequest($request);

        if ($rssForm->isValid()) {
            $em->persist($config);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'flashes.config.notice.rss_updated'
            );

            return $this->redirect($this->generateUrl('config').'#set2');
        }

        // handle tagging rule
        $taggingRule = new TaggingRule();
        $action = $this->generateUrl('config').'#set5';

        if ($request->query->has('tagging-rule')) {
            $taggingRule = $this->getDoctrine()
                ->getRepository('WallabagCoreBundle:TaggingRule')
                ->find($request->query->get('tagging-rule'));

            if ($this->getUser()->getId() !== $taggingRule->getConfig()->getUser()->getId()) {
                return $this->redirect($action);
            }

            $action = $this->generateUrl('config').'?tagging-rule='.$taggingRule->getId().'#set5';
        }

        $newTaggingRule = $this->createForm(TaggingRuleType::class, $taggingRule, ['action' => $action]);
        $newTaggingRule->handleRequest($request);

        if ($newTaggingRule->isValid()) {
            $taggingRule->setConfig($config);
            $em->persist($taggingRule);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'flashes.config.notice.tagging_rules_updated'
            );

            return $this->redirect($this->generateUrl('config').'#set5');
        }

        // handle adding new user
        $newUser = $userManager->createUser();
        // enable created user by default
        $newUser->setEnabled(true);
        $newUserForm = $this->createForm(NewUserType::class, $newUser, [
            'validation_groups' => ['Profile'],
            'action' => $this->generateUrl('config').'#set6',
        ]);
        $newUserForm->handleRequest($request);

        if ($newUserForm->isValid() && $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $userManager->updateUser($newUser);

            // dispatch a created event so the associated config will be created
            $event = new UserEvent($newUser, $request);
            $this->get('event_dispatcher')->dispatch(FOSUserEvents::USER_CREATED, $event);

            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('flashes.config.notice.user_added', ['%username%' => $newUser->getUsername()])
            );

            return $this->redirect($this->generateUrl('config').'#set6');
        }

        return $this->render('WallabagCoreBundle:Config:index.html.twig', [
            'form' => [
                'config' => $configForm->createView(),
                'rss' => $rssForm->createView(),
                'pwd' => $pwdForm->createView(),
                'user' => $userForm->createView(),
                'new_user' => $newUserForm->createView(),
                'new_tagging_rule' => $newTaggingRule->createView(),
            ],
            'rss' => [
                'username' => $user->getUsername(),
                'token' => $config->getRssToken(),
            ],
            'twofactor_auth' => $this->getParameter('twofactor_auth'),
        ]);
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
            return new JsonResponse(['token' => $config->getRssToken()]);
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            'flashes.config.notice.rss_token_updated'
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
        $this->validateRuleAction($rule);

        $em = $this->getDoctrine()->getManager();
        $em->remove($rule);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'flashes.config.notice.tagging_rules_deleted'
        );

        return $this->redirect($this->generateUrl('config').'#set5');
    }

    /**
     * Edit a tagging rule.
     *
     * @param TaggingRule $rule
     *
     * @Route("/tagging-rule/edit/{id}", requirements={"id" = "\d+"}, name="edit_tagging_rule")
     *
     * @return RedirectResponse
     */
    public function editTaggingRuleAction(TaggingRule $rule)
    {
        $this->validateRuleAction($rule);

        return $this->redirect($this->generateUrl('config').'?tagging-rule='.$rule->getId().'#set5');
    }

    /**
     * Validate that a rule can be edited/deleted by the current user.
     *
     * @param TaggingRule $rule
     */
    private function validateRuleAction(TaggingRule $rule)
    {
        if ($this->getUser()->getId() != $rule->getConfig()->getUser()->getId()) {
            throw $this->createAccessDeniedException('You can not access this tagging rule.');
        }
    }

    /**
     * Retrieve config for the current user.
     * If no config were found, create a new one.
     *
     * @return Config
     */
    private function getConfig()
    {
        $config = $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Config')
            ->findOneByUser($this->getUser());

        // should NEVER HAPPEN ...
        if (!$config) {
            $config = new Config($this->getUser());
        }

        return $config;
    }
}
