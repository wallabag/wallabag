<?php

namespace Wallabag\Mailer;

use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Wallabag\Entity\User;

/**
 * Custom mailer for TwoFactorBundle email.
 * It adds a custom template to the email so user won't get a lonely authentication code but a complete email.
 */
class AuthCodeMailer implements AuthCodeMailerInterface
{
    /**
     * @param string $senderEmail sender email address
     * @param string $senderName  sender name
     * @param string $supportUrl  support URL to report any bugs
     */
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        private $senderEmail,
        private $senderName,
        private $supportUrl,
    ) {
    }

    /**
     * Send the auth code to the user via email.
     */
    public function sendAuthCode(TwoFactorInterface $user): void
    {
        \assert($user instanceof User);

        $template = $this->twig->load('TwoFactor/email_auth_code.html.twig');

        $subject = $template->renderBlock('subject', []);
        $bodyHtml = $template->renderBlock('body_html', [
            'user' => $user->getName(),
            'code' => $user->getEmailAuthCode(),
            'support_url' => $this->supportUrl,
        ]);
        $bodyText = $template->renderBlock('body_text', [
            'user' => $user->getName(),
            'code' => $user->getEmailAuthCode(),
            'support_url' => $this->supportUrl,
        ]);

        $email = (new Email())
            ->from(new Address($this->senderEmail, $this->senderName ?: $this->senderEmail))
            ->to($user->getEmailAuthRecipient())
            ->subject($subject)
            ->text($bodyText)
            ->html($bodyHtml);

        $this->mailer->send($email);
    }
}
