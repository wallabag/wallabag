<?php

namespace Wallabag\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseRegistrationFormType;
use Gregwar\CaptchaBundle\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RegistrationFormType extends AbstractType
{
    public function __construct(private readonly bool $captchaEnabled)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($this->captchaEnabled) {
            $builder->add('captcha', CaptchaType::class, [
                'help' => 'captcha.help',
                'label' => false,
                'session_key' => 'public_registration',
                'translation_domain' => 'messages',
            ]);
        }
    }

    public function getParent(): string
    {
        return BaseRegistrationFormType::class;
    }
}
