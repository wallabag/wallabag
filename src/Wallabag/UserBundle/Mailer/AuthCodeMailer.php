<?php

namespace Wallabag\UserBundle\Mailer;

use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;

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
     * Twig to render the html's email.
     *
     * @var \Twig_Environment
     */
    private $twig;

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
     * @param \Swift_Mailer     $mailer
     * @param \Twig_Environment $twig
     * @param string            $senderEmail
     * @param string            $senderName
     * @param string            $supportUrl
     */
    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig, $senderEmail, $senderName, $supportUrl)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
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
        $template = $this->twig->loadTemplate('@WallabagUserBundle/Resources/views/TwoFactor/email_auth_code.html.twig');

        $subject = $template->renderBlock('subject', array());
        $bodyHtml = $template->renderBlock('body_html', [
            'user' => $user->getName(),
            'code' => $user->getEmailAuthCode(),
            'support' => $this->supportUrl,
        ]);
        $bodyText = $template->renderBlock('body_text', [
            'user' => $user->getName(),
            'code' => $user->getEmailAuthCode(),
            'support' => $this->supportUrl,
        ]);

        $message = new \Swift_Message();
        $message
            ->setTo($user->getEmail())
            ->setFrom($this->senderEmail, $this->senderName)
            ->setSubject($subject)
            ->setBody($bodyText, 'text/plain')
            ->addPart($bodyHtml, 'text/html')
        ;

        $this->mailer->send($message);
    }
}
