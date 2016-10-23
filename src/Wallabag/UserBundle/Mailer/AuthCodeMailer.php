<?php

namespace Wallabag\UserBundle\Mailer;

use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Craue\ConfigBundle\Util\Config;

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
     * Url for the wallabag instance (only used for image in the HTML email template).
     *
     * @var string
     */
    private $wallabagUrl;

    /**
     * Initialize the auth code mailer with the SwiftMailer object.
     *
     * @param \Swift_Mailer     $mailer
     * @param \Twig_Environment $twig
     * @param string            $senderEmail
     * @param string            $senderName
     * @param Config            $craueConfig Craue\Config instance to get wallabag support url from database
     */
    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig, $senderEmail, $senderName, Config $craueConfig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
        $this->supportUrl = $craueConfig->get('wallabag_support_url');
        $this->wallabagUrl = $craueConfig->get('wallabag_url');
    }

    /**
     * Send the auth code to the user via email.
     *
     * @param TwoFactorInterface $user
     */
    public function sendAuthCode(TwoFactorInterface $user)
    {
        /** @var \Twig_Template $template */
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
            ->setTo($user->getEmail())
            ->setFrom($this->senderEmail, $this->senderName)
            ->setSubject($subject)
            ->setBody($bodyText, 'text/plain')
            ->addPart($bodyHtml, 'text/html')
        ;

        $this->mailer->send($message);
    }
}
