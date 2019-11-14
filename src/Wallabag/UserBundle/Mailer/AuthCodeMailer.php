<?php

namespace Wallabag\UserBundle\Mailer;

use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Twig\Environment;

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
     * @var Environment
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
     * Url for the wallabag instance (only used for image in the HTML email template).
     *
     * @var string
     */
    private $wallabagUrl;

    /**
     * Initialize the auth code mailer with the SwiftMailer object.
     *
     * @param string $senderEmail
     * @param string $senderName
     * @param string $supportUrl  wallabag support url
     * @param string $wallabagUrl wallabag instance url
     */
    public function __construct(\Swift_Mailer $mailer, Environment $twig, $senderEmail, $senderName, $supportUrl, $wallabagUrl)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
        $this->supportUrl = $supportUrl;
        $this->wallabagUrl = $wallabagUrl;
    }

    /**
     * Send the auth code to the user via email.
     */
    public function sendAuthCode(TwoFactorInterface $user): void
    {
        $template = $this->twig->loadTemplate('WallabagUserBundle:TwoFactor:email_auth_code.html.twig');

        $subject = $template->renderBlock('subject', []);
        $bodyHtml = $template->renderBlock('body_html', [
            'user' => $user->getName(),
            'code' => $user->getEmailAuthCode(),
            'support_url' => $this->supportUrl,
            'wallabag_url' => $this->wallabagUrl,
        ]);
        $bodyText = $template->renderBlock('body_text', [
            'user' => $user->getName(),
            'code' => $user->getEmailAuthCode(),
            'support_url' => $this->supportUrl,
        ]);

        $message = new \Swift_Message();
        $message
            ->setTo($user->getEmailAuthRecipient())
            ->setFrom($this->senderEmail, $this->senderName)
            ->setSubject($subject)
            ->setBody($bodyText, 'text/plain')
            ->addPart($bodyHtml, 'text/html')
        ;

        $this->mailer->send($message);
    }
}
