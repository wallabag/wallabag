<?php

namespace Wallabag\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('new_password', 'repeated', array(
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
        ;
    }

    public function getName()
    {
        return 'change_passwd';
    }
}
