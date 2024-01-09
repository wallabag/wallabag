<?php

namespace Wallabag\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('old_password', PasswordType::class, [
                'constraints' => new UserPassword(['message' => 'validator.password_wrong_value']),
                'label' => 'config.form_password.old_password_label',
            ])
            ->add('new_password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'validator.password_must_match',
                'required' => true,
                'first_options' => ['label' => 'config.form_password.new_password_label'],
                'second_options' => ['label' => 'config.form_password.repeat_new_password_label'],
                'constraints' => [
                    new Length([
                        'min' => 8,
                        'minMessage' => 'validator.password_too_short',
                    ]),
                    new NotBlank(),
                ],
                'label' => 'config.form_password.new_password_label',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'config.form.save',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'change_passwd';
    }
}
