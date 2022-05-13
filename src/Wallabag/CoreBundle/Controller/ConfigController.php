<?php

namespace Wallabag\CoreBundle\Controller;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use PragmaRX\Recovery\Recovery as BackupCodes;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Locale as LocaleConstraint;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\CoreBundle\Entity\IgnoreOriginUserRule;
use Wallabag\CoreBundle\Entity\RuleInterface;
use Wallabag\CoreBundle\Entity\TaggingRule;
use Wallabag\CoreBundle\Form\Type\ChangePasswordType;
use Wallabag\CoreBundle\Form\Type\ConfigType;
use Wallabag\CoreBundle\Form\Type\FeedType;
use Wallabag\CoreBundle\Form\Type\IgnoreOriginUserRuleType;
use Wallabag\CoreBundle\Form\Type\TaggingRuleImportType;
use Wallabag\CoreBundle\Form\Type\TaggingRuleType;
use Wallabag\CoreBundle\Form\Type\UserInformationType;
use Wallabag\CoreBundle\Tools\Utils;

class ConfigController extends Controller
{
    /**
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

        if ($configForm->isSubmitted() && $configForm->isValid()) {
            // force theme to material to avoid using baggy
            if ('baggy' === $config->getTheme()) {
                $config->setTheme('material');

                $this->addFlash(
                    'notice',
                    'Baggy is deprecated, forced to Material theme.'
                );
            }

            $em->persist($config);
            $em->flush();

            $request->getSession()->set('_locale', $config->getLanguage());

            // switch active theme
            $activeTheme = $this->get('liip_theme.active_theme');
            $activeTheme->setName($config->getTheme());

            $this->addFlash(
                'notice',
                'flashes.config.notice.config_saved'
            );

            return $this->redirect($this->generateUrl('config'));
        }

        // handle changing password
        $pwdForm = $this->createForm(ChangePasswordType::class, null, ['action' => $this->generateUrl('config') . '#set4']);
        $pwdForm->handleRequest($request);

        if ($pwdForm->isSubmitted() && $pwdForm->isValid()) {
            if ($this->get('craue_config')->get('demo_mode_enabled') && $this->get('craue_config')->get('demo_mode_username') === $user->getUsername()) {
                $message = 'flashes.config.notice.password_not_updated_demo';
            } else {
                $message = 'flashes.config.notice.password_updated';

                $user->setPlainPassword($pwdForm->get('new_password')->getData());
                $userManager->updateUser($user, true);
            }

            $this->addFlash('notice', $message);

            return $this->redirect($this->generateUrl('config') . '#set4');
        }

        // handle changing user information
        $userForm = $this->createForm(UserInformationType::class, $user, [
            'validation_groups' => ['Profile'],
            'action' => $this->generateUrl('config') . '#set3',
        ]);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $userManager->updateUser($user, true);

            $this->addFlash(
                'notice',
                'flashes.config.notice.user_updated'
            );

            return $this->redirect($this->generateUrl('config') . '#set3');
        }

        // handle feed information
        $feedForm = $this->createForm(FeedType::class, $config, ['action' => $this->generateUrl('config') . '#set2']);
        $feedForm->handleRequest($request);

        if ($feedForm->isSubmitted() && $feedForm->isValid()) {
            $em->persist($config);
            $em->flush();

            $this->addFlash(
                'notice',
                'flashes.config.notice.feed_updated'
            );

            return $this->redirect($this->generateUrl('config') . '#set2');
        }

        // handle tagging rule
        $taggingRule = new TaggingRule();
        $action = $this->generateUrl('config') . '#set5';

        if ($request->query->has('tagging-rule')) {
            $taggingRule = $this->getDoctrine()
                ->getRepository('WallabagCoreBundle:TaggingRule')
                ->find($request->query->get('tagging-rule'));

            if ($this->getUser()->getId() !== $taggingRule->getConfig()->getUser()->getId()) {
                return $this->redirect($action);
            }

            $action = $this->generateUrl('config') . '?tagging-rule=' . $taggingRule->getId() . '#set5';
        }

        $newTaggingRule = $this->createForm(TaggingRuleType::class, $taggingRule, ['action' => $action]);
        $newTaggingRule->handleRequest($request);

        if ($newTaggingRule->isSubmitted() && $newTaggingRule->isValid()) {
            $taggingRule->setConfig($config);
            $em->persist($taggingRule);
            $em->flush();

            $this->addFlash(
                'notice',
                'flashes.config.notice.tagging_rules_updated'
            );

            return $this->redirect($this->generateUrl('config') . '#set5');
        }

        // handle tagging rules import
        $taggingRulesImportform = $this->createForm(TaggingRuleImportType::class);
        $taggingRulesImportform->handleRequest($request);

        if ($taggingRulesImportform->isSubmitted() && $taggingRulesImportform->isValid()) {
            $message = 'flashes.config.notice.tagging_rules_not_imported';
            $file = $taggingRulesImportform->get('file')->getData();

            if (null !== $file && $file->isValid() && \in_array($file->getClientMimeType(), ['application/json', 'application/octet-stream'], true)) {
                $content = json_decode(file_get_contents($file->getPathname()), true);

                if (\is_array($content)) {
                    foreach ($content as $rule) {
                        $taggingRule = new TaggingRule();
                        $taggingRule->setRule($rule['rule']);
                        $taggingRule->setTags($rule['tags']);
                        $taggingRule->setConfig($config);
                        $em->persist($taggingRule);
                    }

                    $em->flush();

                    $message = 'flashes.config.notice.tagging_rules_imported';
                }
            }

            $this->addFlash('notice', $message);

            return $this->redirect($this->generateUrl('config') . '#set5');
        }

        // handle ignore origin rules
        $ignoreOriginUserRule = new IgnoreOriginUserRule();
        $action = $this->generateUrl('config') . '#set6';

        if ($request->query->has('ignore-origin-user-rule')) {
            $ignoreOriginUserRule = $this->getDoctrine()
                ->getRepository('WallabagCoreBundle:IgnoreOriginUserRule')
                ->find($request->query->get('ignore-origin-user-rule'));

            if ($this->getUser()->getId() !== $ignoreOriginUserRule->getConfig()->getUser()->getId()) {
                return $this->redirect($action);
            }

            $action = $this->generateUrl('config', [
                'ignore-origin-user-rule' => $ignoreOriginUserRule->getId(),
            ]) . '#set6';
        }

        $newIgnoreOriginUserRule = $this->createForm(IgnoreOriginUserRuleType::class, $ignoreOriginUserRule, ['action' => $action]);
        $newIgnoreOriginUserRule->handleRequest($request);

        if ($newIgnoreOriginUserRule->isSubmitted() && $newIgnoreOriginUserRule->isValid()) {
            $ignoreOriginUserRule->setConfig($config);
            $em->persist($ignoreOriginUserRule);
            $em->flush();

            $this->addFlash(
                'notice',
                'flashes.config.notice.ignore_origin_rules_updated'
            );

            return $this->redirect($this->generateUrl('config') . '#set6');
        }

        return $this->render('WallabagCoreBundle:Config:index.html.twig', [
            'form' => [
                'config' => $configForm->createView(),
                'feed' => $feedForm->createView(),
                'pwd' => $pwdForm->createView(),
                'user' => $userForm->createView(),
                'new_tagging_rule' => $newTaggingRule->createView(),
                'import_tagging_rule' => $taggingRulesImportform->createView(),
                'new_ignore_origin_user_rule' => $newIgnoreOriginUserRule->createView(),
            ],
            'feed' => [
                'username' => $user->getUsername(),
                'token' => $config->getFeedToken(),
            ],
            'twofactor_auth' => $this->getParameter('twofactor_auth'),
            'wallabag_url' => $this->getParameter('domain_name'),
            'enabled_users' => $this->get('wallabag_user.user_repository')->getSumEnabledUsers(),
        ]);
    }

    /**
     * Disable 2FA using email.
     *
     * @Route("/config/otp/email/disable", name="disable_otp_email")
     */
    public function disableOtpEmailAction()
    {
        if (!$this->getParameter('twofactor_auth')) {
            return $this->createNotFoundException('two_factor not enabled');
        }

        $user = $this->getUser();
        $user->setEmailTwoFactor(false);

        $this->container->get('fos_user.user_manager')->updateUser($user, true);

        $this->addFlash(
            'notice',
            'flashes.config.notice.otp_disabled'
        );

        return $this->redirect($this->generateUrl('config') . '#set3');
    }

    /**
     * Enable 2FA using email.
     *
     * @Route("/config/otp/email", name="config_otp_email")
     */
    public function otpEmailAction()
    {
        if (!$this->getParameter('twofactor_auth')) {
            return $this->createNotFoundException('two_factor not enabled');
        }

        $user = $this->getUser();

        $user->setGoogleAuthenticatorSecret(null);
        $user->setBackupCodes(null);
        $user->setEmailTwoFactor(true);

        $this->container->get('fos_user.user_manager')->updateUser($user, true);

        $this->addFlash(
            'notice',
            'flashes.config.notice.otp_enabled'
        );

        return $this->redirect($this->generateUrl('config') . '#set3');
    }

    /**
     * Disable 2FA using OTP app.
     *
     * @Route("/config/otp/app/disable", name="disable_otp_app")
     */
    public function disableOtpAppAction()
    {
        if (!$this->getParameter('twofactor_auth')) {
            return $this->createNotFoundException('two_factor not enabled');
        }

        $user = $this->getUser();

        $user->setGoogleAuthenticatorSecret('');
        $user->setBackupCodes(null);

        $this->container->get('fos_user.user_manager')->updateUser($user, true);

        $this->addFlash(
            'notice',
            'flashes.config.notice.otp_disabled'
        );

        return $this->redirect($this->generateUrl('config') . '#set3');
    }

    /**
     * Enable 2FA using OTP app, user will need to confirm the generated code from the app.
     *
     * @Route("/config/otp/app", name="config_otp_app")
     */
    public function otpAppAction()
    {
        if (!$this->getParameter('twofactor_auth')) {
            return $this->createNotFoundException('two_factor not enabled');
        }

        $user = $this->getUser();
        $secret = $this->get('scheb_two_factor.security.google_authenticator')->generateSecret();

        $user->setGoogleAuthenticatorSecret($secret);
        $user->setEmailTwoFactor(false);

        $backupCodes = (new BackupCodes())->toArray();
        $backupCodesHashed = array_map(
            function ($backupCode) {
                return password_hash($backupCode, \PASSWORD_DEFAULT);
            },
            $backupCodes
        );

        $user->setBackupCodes($backupCodesHashed);

        $this->container->get('fos_user.user_manager')->updateUser($user, true);

        $this->addFlash(
            'notice',
            'flashes.config.notice.otp_enabled'
        );

        return $this->render('WallabagCoreBundle:Config:otp_app.html.twig', [
            'backupCodes' => $backupCodes,
            'qr_code' => $this->get('scheb_two_factor.security.google_authenticator')->getQRContent($user),
            'secret' => $secret,
        ]);
    }

    /**
     * Cancelling 2FA using OTP app.
     *
     * @Route("/config/otp/app/cancel", name="config_otp_app_cancel")
     */
    public function otpAppCancelAction()
    {
        if (!$this->getParameter('twofactor_auth')) {
            return $this->createNotFoundException('two_factor not enabled');
        }

        $user = $this->getUser();
        $user->setGoogleAuthenticatorSecret(null);
        $user->setBackupCodes(null);

        $this->container->get('fos_user.user_manager')->updateUser($user, true);

        return $this->redirect($this->generateUrl('config') . '#set3');
    }

    /**
     * Validate OTP code.
     *
     * @Route("/config/otp/app/check", name="config_otp_app_check")
     */
    public function otpAppCheckAction(Request $request)
    {
        $isValid = $this->get('scheb_two_factor.security.google_authenticator')->checkCode(
            $this->getUser(),
            $request->get('_auth_code')
        );

        if (true === $isValid) {
            $this->addFlash(
                'notice',
                'flashes.config.notice.otp_enabled'
            );

            return $this->redirect($this->generateUrl('config') . '#set3');
        }

        $this->addFlash(
            'two_factor',
            'scheb_two_factor.code_invalid'
        );

        return $this->redirect($this->generateUrl('config_otp_app'));
    }

    /**
     * @Route("/generate-token", name="generate_token")
     *
     * @return RedirectResponse|JsonResponse
     */
    public function generateTokenAction(Request $request)
    {
        $config = $this->getConfig();
        $config->setFeedToken(Utils::generateToken());

        $em = $this->getDoctrine()->getManager();
        $em->persist($config);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['token' => $config->getFeedToken()]);
        }

        $this->addFlash(
            'notice',
            'flashes.config.notice.feed_token_updated'
        );

        return $this->redirect($this->generateUrl('config') . '#set2');
    }

    /**
     * @Route("/revoke-token", name="revoke_token")
     *
     * @return RedirectResponse|JsonResponse
     */
    public function revokeTokenAction(Request $request)
    {
        $config = $this->getConfig();
        $config->setFeedToken(null);

        $em = $this->getDoctrine()->getManager();
        $em->persist($config);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse();
        }

        $this->addFlash(
            'notice',
            'flashes.config.notice.feed_token_revoked'
        );

        return $this->redirect($this->generateUrl('config') . '#set2');
    }

    /**
     * Deletes a tagging rule and redirect to the config homepage.
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

        $this->addFlash(
            'notice',
            'flashes.config.notice.tagging_rules_deleted'
        );

        return $this->redirect($this->generateUrl('config') . '#set5');
    }

    /**
     * Edit a tagging rule.
     *
     * @Route("/tagging-rule/edit/{id}", requirements={"id" = "\d+"}, name="edit_tagging_rule")
     *
     * @return RedirectResponse
     */
    public function editTaggingRuleAction(TaggingRule $rule)
    {
        $this->validateRuleAction($rule);

        return $this->redirect($this->generateUrl('config') . '?tagging-rule=' . $rule->getId() . '#set5');
    }

    /**
     * Deletes an ignore origin rule and redirect to the config homepage.
     *
     * @Route("/ignore-origin-user-rule/delete/{id}", requirements={"id" = "\d+"}, name="delete_ignore_origin_rule")
     *
     * @return RedirectResponse
     */
    public function deleteIgnoreOriginRuleAction(IgnoreOriginUserRule $rule)
    {
        $this->validateRuleAction($rule);

        $em = $this->getDoctrine()->getManager();
        $em->remove($rule);
        $em->flush();

        $this->addFlash(
            'notice',
            'flashes.config.notice.ignore_origin_rules_deleted'
        );

        return $this->redirect($this->generateUrl('config') . '#set6');
    }

    /**
     * Edit an ignore origin rule.
     *
     * @Route("/ignore-origin-user-rule/edit/{id}", requirements={"id" = "\d+"}, name="edit_ignore_origin_rule")
     *
     * @return RedirectResponse
     */
    public function editIgnoreOriginRuleAction(IgnoreOriginUserRule $rule)
    {
        $this->validateRuleAction($rule);

        return $this->redirect($this->generateUrl('config') . '?ignore-origin-user-rule=' . $rule->getId() . '#set6');
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
                // SQLite doesn't care about cascading remove, so we need to manually remove associated stuff
                // otherwise they won't be removed ...
                if ($this->get('doctrine')->getConnection()->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform) {
                    $this->getDoctrine()->getRepository('WallabagAnnotationBundle:Annotation')->removeAllByUserId($this->getUser()->getId());
                }

                // manually remove tags to avoid orphan tag
                $this->removeAllTagsByUserId($this->getUser()->getId());

                $this->get('wallabag_core.entry_repository')->removeAllByUserId($this->getUser()->getId());
                break;
            case 'archived':
                if ($this->get('doctrine')->getConnection()->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform) {
                    $this->removeAnnotationsForArchivedByUserId($this->getUser()->getId());
                }

                // manually remove tags to avoid orphan tag
                $this->removeTagsForArchivedByUserId($this->getUser()->getId());

                $this->get('wallabag_core.entry_repository')->removeArchivedByUserId($this->getUser()->getId());
                break;
        }

        $this->addFlash(
            'notice',
            'flashes.config.notice.' . $type . '_reset'
        );

        return $this->redirect($this->generateUrl('config') . '#set3');
    }

    /**
     * Delete account for current user.
     *
     * @Route("/account/delete", name="delete_account")
     *
     * @throws AccessDeniedHttpException
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAccountAction(Request $request)
    {
        $enabledUsers = $this->get('wallabag_user.user_repository')
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

    /**
     * Switch view mode for current user.
     *
     * @Route("/config/view-mode", name="switch_view_mode")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeViewModeAction(Request $request)
    {
        $user = $this->getUser();
        $user->getConfig()->setListMode(!$user->getConfig()->getListMode());

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Change the locale for the current user.
     *
     * @param string $language
     *
     * @Route("/locale/{language}", name="changeLocale")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function setLocaleAction(Request $request, $language = null)
    {
        $errors = $this->get('validator')->validate($language, (new LocaleConstraint()));

        if (0 === \count($errors)) {
            $request->getSession()->set('_locale', $language);
        }

        return $this->redirect($request->headers->get('referer', $this->generateUrl('homepage')));
    }

    /**
     * Export tagging rules for the logged in user.
     *
     * @Route("/tagging-rule/export", name="export_tagging_rule")
     *
     * @return Response
     */
    public function exportTaggingRulesAction()
    {
        $data = SerializerBuilder::create()->build()->serialize(
            $this->getUser()->getConfig()->getTaggingRules(),
            'json',
            SerializationContext::create()->setGroups(['export_tagging_rule'])
        );

        return Response::create(
            $data,
            200,
            [
                'Content-type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="tagging_rules_' . $this->getUser()->getUsername() . '.json"',
                'Content-Transfer-Encoding' => 'UTF-8',
            ]
        );
    }

    /**
     * Remove all tags for given tags and a given user and cleanup orphan tags.
     *
     * @param array $tags
     * @param int   $userId
     */
    private function removeAllTagsByStatusAndUserId($tags, $userId)
    {
        if (empty($tags)) {
            return;
        }

        $this->get('wallabag_core.entry_repository')
            ->removeTags($userId, $tags);

        // cleanup orphan tags
        $em = $this->getDoctrine()->getManager();

        foreach ($tags as $tag) {
            if (0 === \count($tag->getEntries())) {
                $em->remove($tag);
            }
        }

        $em->flush();
    }

    /**
     * Remove all tags for a given user and cleanup orphan tags.
     *
     * @param int $userId
     */
    private function removeAllTagsByUserId($userId)
    {
        $tags = $this->get('wallabag_core.tag_repository')->findAllTags($userId);
        $this->removeAllTagsByStatusAndUserId($tags, $userId);
    }

    /**
     * Remove all tags for a given user and cleanup orphan tags.
     *
     * @param int $userId
     */
    private function removeTagsForArchivedByUserId($userId)
    {
        $tags = $this->get('wallabag_core.tag_repository')->findForArchivedArticlesByUser($userId);
        $this->removeAllTagsByStatusAndUserId($tags, $userId);
    }

    private function removeAnnotationsForArchivedByUserId($userId)
    {
        $em = $this->getDoctrine()->getManager();

        $archivedEntriesAnnotations = $this->getDoctrine()
            ->getRepository('WallabagAnnotationBundle:Annotation')
            ->findAllArchivedEntriesByUser($userId);

        foreach ($archivedEntriesAnnotations as $archivedEntriesAnnotation) {
            $em->remove($archivedEntriesAnnotation);
        }

        $em->flush();
    }

    /**
     * Validate that a rule can be edited/deleted by the current user.
     */
    private function validateRuleAction(RuleInterface $rule)
    {
        if ($this->getUser()->getId() !== $rule->getConfig()->getUser()->getId()) {
            throw $this->createAccessDeniedException('You can not access this rule.');
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
