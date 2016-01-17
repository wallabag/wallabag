<?php

namespace Wallabag\UserBundle\Mailer;

use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Custom mailer for TwoFactorBundle email.
 * It adds a custom template to the email so user won't get a lonely authentication code but a complete email.
 */
class AuthCodeMailer implements AuthCodeMailerInterface
{
    /**
     * SwiftMailer.
     *
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * Translator for email content.
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Sender email address.
     *
     * @var string
     */
    private $senderEmail;

    /**
     * Sender name.
     *
     * @var string
     */
    private $senderName;

    /**
     * Support URL to report any bugs.
     *
     * @var string
     */
    private $supportUrl;

    /**
     * Initialize the auth code mailer with the SwiftMailer object.
     *
     * @param \Swift_Mailer       $mailer
     * @param TranslatorInterface $translator
     * @param string              $senderEmail
     * @param string              $senderName
     * @param string              $supportUrl
     */
    public function __construct(\Swift_Mailer $mailer, TranslatorInterface $translator, $senderEmail, $senderName, $supportUrl)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
        $this->supportUrl = $supportUrl;
    }

    /**
     * Send the auth code to the user via email.
     *
     * @param TwoFactorInterface $user
     */
    public function sendAuthCode(TwoFactorInterface $user)
    {
        $message = new \Swift_Message();
        $message
            ->setTo($user->getEmail())
            ->setFrom($this->senderEmail, $this->senderName)
            ->setSubject($this->translator->trans('auth_code.mailer.subject', array(), 'wallabag_user'))
            ->setBody($this->translator->trans(
                'auth_code.mailer.body',
                [
                    '%user%' => $user->getName(),
                    '%code%' => $user->getEmailAuthCode(),
                    '%support%' => $this->supportUrl,
                ],
                'wallabag_user'
            ))
        ;

        $this->mailer->send($message);
    }
}
