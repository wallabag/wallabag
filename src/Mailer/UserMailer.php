<?php

namespace Wallabag\Mailer;

use FOS\UserBundle\Mailer\TwigSwiftMailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * This replace the default mailer from TwigSwiftMailer by symfony/mailer instead of swiftmailer.
 */
class UserMailer extends TwigSwiftMailer
{
    /**
     * @var MailerInterface
     */
    protected $mailer;

    /**
     * @var UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var array{template: array{confirmation: string, resetting: string}, from_email: array{confirmation: array<string, string>|string, resetting: array<string, string>|string}}
     */
    protected $parameters;

    public function __construct(MailerInterface $mailer, UrlGeneratorInterface $router, Environment $twig, array $parameters)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
        $this->parameters = $parameters;
    }

    /**
     * @param string $templateName
     * @param array  $context
     * @param array  $fromEmail
     * @param string $toEmail
     */
    protected function sendMessage($templateName, $context, $fromEmail, $toEmail)
    {
        $template = $this->twig->load($templateName);
        $subject = $template->renderBlock('subject', $context);
        $textBody = $template->renderBlock('body_text', $context);

        $htmlBody = '';

        if ($template->hasBlock('body_html', $context)) {
            $htmlBody = $template->renderBlock('body_html', $context);
        }

        $email = (new Email())
            ->from(new Address(key($fromEmail), current($fromEmail)))
            ->to($toEmail)
            ->subject($subject);

        if (!empty($htmlBody)) {
            $email
                ->text($textBody)
                ->html($htmlBody);
        } else {
            $email->text($textBody);
        }

        $this->mailer->send($email);
    }
}
