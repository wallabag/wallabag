<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\CoreBundle\Entity\TaggingRule;
use Wallabag\CoreBundle\Form\Type\ConfigType;
use Wallabag\CoreBundle\Form\Type\ChangePasswordType;
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

        return $this->render('WallabagCoreBundle:Config:index.html.twig', [
            'form' => [
                'config' => $configForm->createView(),
                'rss' => $rssForm->createView(),
                'pwd' => $pwdForm->createView(),
                'user' => $userForm->createView(),
                'new_tagging_rule' => $newTaggingRule->createView(),
            ],
            'rss' => [
                'username' => $user->getUsername(),
                'token' => $config->getRssToken(),
            ],
            'twofactor_auth' => $this->getParameter('twofactor_auth'),
            'enabled_users' => $this->getDoctrine()
                ->getRepository('WallabagUserBundle:User')
                ->getSumEnabledUsers(),
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
     * Remove all annotations OR tags OR entries for the current user.
     *
     * @Route("/reset/{type}", requirements={"id" = "annotations|tags|entries"}, name="config_reset")
     *
     * @return RedirectResponse
     */
    public function resetAction($type)
    {
        $em = $this->getDoctrine()->getManager();

        switch ($type) {
            case 'annotations':
                $this->getDoctrine()
                    ->getRepository('WallabagAnnotationBundle:Annotation')
                    ->removeAllByUserId($this->getUser()->getId());
                break;

            case 'tags':
                $this->removeAllTagsByUserId($this->getUser()->getId());
                break;

            case 'entries':
                // SQLite doesn't care about cascading remove, so we need to manually remove associated stuf
                // otherwise they won't be removed ...
                if ($this->get('doctrine')->getConnection()->getDriver() instanceof \Doctrine\DBAL\Driver\PDOSqlite\Driver) {
                    $this->getDoctrine()->getRepository('WallabagAnnotationBundle:Annotation')->removeAllByUserId($this->getUser()->getId());
                }

                // manually remove tags first to avoid orphan tag
                $this->removeAllTagsByUserId($this->getUser()->getId());

                $this->getDoctrine()
                    ->getRepository('WallabagCoreBundle:Entry')
                    ->removeAllByUserId($this->getUser()->getId());
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            'flashes.config.notice.'.$type.'_reset'
        );

        return $this->redirect($this->generateUrl('config').'#set3');
    }

    /**
     * Remove all tags for a given user and cleanup orphan tags
     *
     * @param  int $userId
     */
    private function removeAllTagsByUserId($userId)
    {
        $tags = $this->getDoctrine()->getRepository('WallabagCoreBundle:Tag')->findAllTags($userId);

        if (empty($tags)) {
            return;
        }

        $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
            ->removeTags($userId, $tags);

        $em = $this->getDoctrine()->getManager();

        foreach ($tags as $tag) {
            if (count($tag->getEntries()) === 0) {
                $em->remove($tag);
            }
        }

        $em->flush();
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

    /**
     * Delete account for current user.
     *
     * @Route("/account/delete", name="delete_account")
     *
     * @param Request $request
     *
     * @throws AccessDeniedHttpException
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAccountAction(Request $request)
    {
        $enabledUsers = $this->getDoctrine()
            ->getRepository('WallabagUserBundle:User')
            ->getSumEnabledUsers();

        if ($enabledUsers <= 1) {
            throw new AccessDeniedHttpException();
        }

        $user = $this->getUser();

        // logout current user
        $this->get('security.token_storage')->setToken(null);
        $request->getSession()->invalidate();

        $em = $this->get('fos_user.user_manager');
        $em->deleteUser($user);

        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }
}
