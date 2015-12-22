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
                'constraints' => new UserPassword(array('message' => 'Wrong value for your current password')),
            ))
            ->add('new_password', RepeatedType::class, array(
                'type' => 'password',
                'invalid_message' => 'The password fields must match.',
                'required' => true,
                'first_options' => array('label' => 'New password'),
                'second_options' => array('label' => 'Repeat new password'),
                'constraints' => array(
                    new Constraints\Length(array(
                        'min' => 8,
                        'minMessage' => 'Password should by at least 8 chars long',
                    )),
                    new Constraints\NotBlank(),
                ),
            ))
            ->add('save', SubmitType::class)
        ;
    }

    public function getBlockPrefix()
    {
        return 'change_passwd';
    }
}
