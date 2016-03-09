<?php

namespace Wallabag\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('old_password', PasswordType::class, array(
                'constraints' => new UserPassword(array('message' => 'validator.password_wrong_value')),
                'label' => 'config.form_password.old_password_label',
            ))
            ->add('new_password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'validator.password_must_match',
                'required' => true,
                'first_options' => array('label' => 'config.form_password.new_password_label'),
                'second_options' => array('label' => 'config.form_password.repeat_new_password_label'),
                'constraints' => array(
                    new Constraints\Length(array(
                        'min' => 8,
                        'minMessage' => 'validator.password_too_short',
                    )),
                    new Constraints\NotBlank(),
                ),
                'label' => 'config.form_password.new_password_label',
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'config.form.save',
            ))
        ;
    }

    public function getBlockPrefix()
    {
        return 'change_passwd';
    }
}
