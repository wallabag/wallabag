<?php

namespace Wallabag\Controller;

use Craue\ConfigBundle\Util\Config;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use PragmaRX\Recovery\Recovery as BackupCodes;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\Locale as LocaleConstraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Wallabag\Entity\Config as ConfigEntity;
use Wallabag\Entity\IgnoreOriginUserRule;
use Wallabag\Entity\RuleInterface;
use Wallabag\Entity\TaggingRule;
use Wallabag\Event\ConfigUpdatedEvent;
use Wallabag\Form\Type\ChangePasswordType;
use Wallabag\Form\Type\ConfigType;
use Wallabag\Form\Type\FeedType;
use Wallabag\Form\Type\IgnoreOriginUserRuleType;
use Wallabag\Form\Type\TaggingRuleImportType;
use Wallabag\Form\Type\TaggingRuleType;
use Wallabag\Form\Type\UserInformationType;
use Wallabag\Helper\Redirect;
use Wallabag\Repository\AnnotationRepository;
use Wallabag\Repository\ConfigRepository;
use Wallabag\Repository\EntryRepository;
use Wallabag\Repository\IgnoreOriginUserRuleRepository;
use Wallabag\Repository\TaggingRuleRepository;
use Wallabag\Repository\TagRepository;
use Wallabag\Repository\UserRepository;
use Wallabag\Tools\Utils;

class ConfigController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserManagerInterface $userManager;
    private EntryRepository $entryRepository;
    private TagRepository $tagRepository;
    private AnnotationRepository $annotationRepository;
    private ConfigRepository $configRepository;
    private EventDispatcherInterface $eventDispatcher;
    private Redirect $redirectHelper;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserManagerInterface $userManager,
        EntryRepository $entryRepository,
        TagRepository $tagRepository,
        AnnotationRepository $annotationRepository,
        ConfigRepository $configRepository,
        EventDispatcherInterface $eventDispatcher,
        Redirect $redirectHelper
    ) {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->entryRepository = $entryRepository;
        $this->tagRepository = $tagRepository;
        $this->annotationRepository = $annotationRepository;
        $this->configRepository = $configRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->redirectHelper = $redirectHelper;
    }

    /**
     * @Route("/config", name="config")
     */
    public function indexAction(Request $request, Config $craueConfig, TaggingRuleRepository $taggingRuleRepository, IgnoreOriginUserRuleRepository $ignoreOriginUserRuleRepository, UserRepository $userRepository)
    {
        $config = $this->getConfig();
        $user = $this->getUser();

        // handle basic config detail (this form is defined as a service)
        $configForm = $this->createForm(ConfigType::class, $config, ['action' => $this->generateUrl('config')]);
        $configForm->handleRequest($request);

        if ($configForm->isSubmitted() && $configForm->isValid()) {
            $this->eventDispatcher->dispatch(new ConfigUpdatedEvent($config), ConfigUpdatedEvent::NAME);
            $this->entityManager->persist($config);
            $this->entityManager->flush();

            $request->getSession()->set('_locale', $config->getLanguage());

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
            $message = 'flashes.config.notice.password_updated';

            $user->setPlainPassword($pwdForm->get('new_password')->getData());
            $this->userManager->updateUser($user, true);

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
            $this->userManager->updateUser($user, true);

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
            $this->entityManager->persist($config);
            $this->entityManager->flush();

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
            $taggingRule = $taggingRuleRepository->find($request->query->get('tagging-rule'));

            if ($this->getUser()->getId() !== $taggingRule->getConfig()->getUser()->getId()) {
                return $this->redirect($action);
            }

            $action = $this->generateUrl('config') . '?tagging-rule=' . $taggingRule->getId() . '#set5';
        }

        $newTaggingRule = $this->createForm(TaggingRuleType::class, $taggingRule, ['action' => $action]);
        $newTaggingRule->handleRequest($request);

        if ($newTaggingRule->isSubmitted() && $newTaggingRule->isValid()) {
            $taggingRule->setConfig($config);
            $this->entityManager->persist($taggingRule);
            $this->entityManager->flush();

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
                        $this->entityManager->persist($taggingRule);
                    }

                    $this->entityManager->flush();

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
            $ignoreOriginUserRule = $ignoreOriginUserRuleRepository
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
            $this->entityManager->persist($ignoreOriginUserRule);
            $this->entityManager->flush();

            $this->addFlash(
                'notice',
                'flashes.config.notice.ignore_origin_rules_updated'
            );

            return $this->redirect($this->generateUrl('config') . '#set6');
        }

        return $this->render('Config/index.html.twig', [
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
            'enabled_users' => $userRepository->getSumEnabledUsers(),
        ]);
    }

    /**
     * Disable 2FA using email.
     *
     * @Route("/config/otp/email/disable", name="disable_otp_email", methods={"POST"})
     */
    public function disableOtpEmailAction(Request $request)
    {
        if (!$this->isCsrfTokenValid('otp', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Bad CSRF token.');
        }

        $user = $this->getUser();
        $user->setEmailTwoFactor(false);

        $this->userManager->updateUser($user, true);

        $this->addFlash(
            'notice',
            'flashes.config.notice.otp_disabled'
        );

        return $this->redirect($this->generateUrl('config') . '#set3');
    }

    /**
     * Enable 2FA using email.
     *
     * @Route("/config/otp/email", name="config_otp_email", methods={"POST"})
     */
    public function otpEmailAction(Request $request)
    {
        if (!$this->isCsrfTokenValid('otp', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Bad CSRF token.');
        }

        $user = $this->getUser();

        $user->setGoogleAuthenticatorSecret(null);
        $user->setBackupCodes(null);
        $user->setEmailTwoFactor(true);

        $this->userManager->updateUser($user, true);

        $this->addFlash(
            'notice',
            'flashes.config.notice.otp_enabled'
        );

        return $this->redirect($this->generateUrl('config') . '#set3');
    }

    /**
     * Disable 2FA using OTP app.
     *
     * @Route("/config/otp/app/disable", name="disable_otp_app", methods={"POST"})
     */
    public function disableOtpAppAction(Request $request)
    {
        if (!$this->isCsrfTokenValid('otp', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Bad CSRF token.');
        }

        $user = $this->getUser();

        $user->setGoogleAuthenticatorSecret('');
        $user->setBackupCodes(null);

        $this->userManager->updateUser($user, true);

        $this->addFlash(
            'notice',
            'flashes.config.notice.otp_disabled'
        );

        return $this->redirect($this->generateUrl('config') . '#set3');
    }

    /**
     * Enable 2FA using OTP app, user will need to confirm the generated code from the app.
     *
     * @Route("/config/otp/app", name="config_otp_app", methods={"POST"})
     */
    public function otpAppAction(Request $request, GoogleAuthenticatorInterface $googleAuthenticator)
    {
        if (!$this->isCsrfTokenValid('otp', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Bad CSRF token.');
        }

        $user = $this->getUser();
        $secret = $googleAuthenticator->generateSecret();

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

        $this->userManager->updateUser($user, true);

        $this->addFlash(
            'notice',
            'flashes.config.notice.otp_enabled'
        );

        return $this->render('Config/otp_app.html.twig', [
            'backupCodes' => $backupCodes,
            'qr_code' => $googleAuthenticator->getQRContent($user),
            'secret' => $secret,
        ]);
    }

    /**
     * Cancelling 2FA using OTP app.
     *
     * @Route("/config/otp/app/cancel", name="config_otp_app_cancel")
     *
     * XXX: commented until we rewrite 2fa with a real two-steps activation
     */
    /*public function otpAppCancelAction()
    {
        $user = $this->getUser();
        $user->setGoogleAuthenticatorSecret(null);
        $user->setBackupCodes(null);

        $this->userManager->updateUser($user, true);

        return $this->redirect($this->generateUrl('config') . '#set3');
    }*/

    /**
     * Validate OTP code.
     *
     * @Route("/config/otp/app/check", name="config_otp_app_check", methods={"POST"})
     */
    public function otpAppCheckAction(Request $request, GoogleAuthenticatorInterface $googleAuthenticator)
    {
        if (!$this->isCsrfTokenValid('otp', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Bad CSRF token.');
        }

        $isValid = $googleAuthenticator->checkCode(
            $this->getUser(),
            $request->request->get('_auth_code')
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

        $this->addFlash(
            'notice',
            'scheb_two_factor.code_invalid'
        );

        return $this->redirect($this->generateUrl('config') . '#set3');
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

        $this->entityManager->persist($config);
        $this->entityManager->flush();

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

        $this->entityManager->persist($config);
        $this->entityManager->flush();

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

        $this->entityManager->remove($rule);
        $this->entityManager->flush();

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

        $this->entityManager->remove($rule);
        $this->entityManager->flush();

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
     * @Route("/reset/{type}", requirements={"id" = "annotations|tags|entries|tagging_rules"}, name="config_reset", methods={"POST"})
     *
     * @return RedirectResponse
     */
    public function resetAction(Request $request, string $type, AnnotationRepository $annotationRepository, EntryRepository $entryRepository, TaggingRuleRepository $taggingRuleRepository)
    {
        if (!$this->isCsrfTokenValid('reset-area', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Bad CSRF token.');
        }

        switch ($type) {
            case 'annotations':
                $annotationRepository->removeAllByUserId($this->getUser()->getId());
                break;
            case 'tagging_rules':
                $taggingRuleRepository->removeAllByConfigId($this->getConfig()->getId());
                break;
            case 'tags':
                $this->removeAllTagsByUserId($this->getUser()->getId());
                break;
            case 'entries':
                // SQLite doesn't care about cascading remove, so we need to manually remove associated stuff
                // otherwise they won't be removed ...
                if ($this->entityManager->getConnection()->getDatabasePlatform() instanceof SqlitePlatform) {
                    $annotationRepository->removeAllByUserId($this->getUser()->getId());
                }

                // manually remove tags to avoid orphan tag
                $this->removeAllTagsByUserId($this->getUser()->getId());

                $entryRepository->removeAllByUserId($this->getUser()->getId());
                break;
            case 'archived':
                if ($this->entityManager->getConnection()->getDatabasePlatform() instanceof SqlitePlatform) {
                    $this->removeAnnotationsForArchivedByUserId($this->getUser()->getId());
                }

                // manually remove tags to avoid orphan tag
                $this->removeTagsForArchivedByUserId($this->getUser()->getId());

                $entryRepository->removeArchivedByUserId($this->getUser()->getId());
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
     * @Route("/account/delete", name="delete_account", methods={"POST"})
     *
     * @throws AccessDeniedHttpException
     *
     * @return RedirectResponse
     */
    public function deleteAccountAction(Request $request, UserRepository $userRepository, TokenStorageInterface $tokenStorage)
    {
        if (!$this->isCsrfTokenValid('delete-account', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Bad CSRF token.');
        }

        $enabledUsers = $userRepository->getSumEnabledUsers();

        if ($enabledUsers <= 1) {
            throw new AccessDeniedHttpException();
        }

        $user = $this->getUser();

        // logout current user
        $tokenStorage->setToken(null);
        $request->getSession()->invalidate();

        $this->userManager->deleteUser($user);

        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }

    /**
     * Switch view mode for current user.
     *
     * @Route("/config/view-mode", name="switch_view_mode")
     *
     * @return RedirectResponse
     */
    public function changeViewModeAction(Request $request)
    {
        $user = $this->getUser();
        $user->getConfig()->setListMode(!$user->getConfig()->getListMode());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $redirectUrl = $this->redirectHelper->to($request->query->get('redirect'));

        return $this->redirect($redirectUrl);
    }

    /**
     * Change the locale for the current user.
     *
     * @param string $language
     *
     * @Route("/locale/{language}", name="changeLocale")
     *
     * @return RedirectResponse
     */
    public function setLocaleAction(Request $request, ValidatorInterface $validator, $language = null)
    {
        $errors = $validator->validate($language, new LocaleConstraint(['canonicalize' => true]));

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

        return new Response(
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

        $this->entryRepository->removeTags($userId, $tags);

        // cleanup orphan tags
        foreach ($tags as $tag) {
            if (0 === \count($tag->getEntries())) {
                $this->entityManager->remove($tag);
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Remove all tags for a given user and cleanup orphan tags.
     *
     * @param int $userId
     */
    private function removeAllTagsByUserId($userId)
    {
        $tags = $this->tagRepository->findAllTags($userId);
        $this->removeAllTagsByStatusAndUserId($tags, $userId);
    }

    /**
     * Remove all tags for a given user and cleanup orphan tags.
     *
     * @param int $userId
     */
    private function removeTagsForArchivedByUserId($userId)
    {
        $tags = $this->tagRepository->findForArchivedArticlesByUser($userId);
        $this->removeAllTagsByStatusAndUserId($tags, $userId);
    }

    private function removeAnnotationsForArchivedByUserId($userId)
    {
        $archivedEntriesAnnotations = $this->annotationRepository
            ->findAllArchivedEntriesByUser($userId);

        foreach ($archivedEntriesAnnotations as $archivedEntriesAnnotation) {
            $this->entityManager->remove($archivedEntriesAnnotation);
        }

        $this->entityManager->flush();
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
     * @return ConfigEntity
     */
    private function getConfig()
    {
        $config = $this->configRepository->findOneByUser($this->getUser());

        // should NEVER HAPPEN ...
        if (!$config) {
            $config = new ConfigEntity($this->getUser());
        }

        return $config;
    }
}
