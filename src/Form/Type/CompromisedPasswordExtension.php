<?php

namespace Wallabag\Form\Type;

use FOS\UserBundle\Form\Type\ChangePasswordFormType;
use FOS\UserBundle\Form\Type\RegistrationFormType;
use FOS\UserBundle\Form\Type\ResettingFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;

class CompromisedPasswordExtension extends AbstractTypeExtension
{
    public function __construct(private readonly bool $checkCompromisedPasswords)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$this->checkCompromisedPasswords) {
            return;
        }

        $passwordOptions = $builder->get('plainPassword')->getOptions();
        $passwordOptions['constraints'][] = new NotCompromisedPassword(['skipOnError' => true]);
        $builder->add('plainPassword', RepeatedType::class, $passwordOptions);
    }

    public static function getExtendedTypes(): iterable
    {
        return [RegistrationFormType::class, ResettingFormType::class, ChangePasswordFormType::class];
    }
}
