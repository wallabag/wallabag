<?php

namespace Wallabag\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class NewUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, array(
                'required' => true,
                'label' => 'config.form_new_user.username_label',
            ))
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'validator.password_must_match',
                'first_options' => array('label' => 'config.form_new_user.password_label'),
                'second_options' => array('label' => 'config.form_new_user.repeat_new_password_label'),
                'constraints' => array(
                    new Constraints\Length(array(
                        'min' => 8,
                        'minMessage' => 'validator.password_too_short',
                    )),
                    new Constraints\NotBlank(),
                ),
                'label' => 'config.form_new_user.plain_password_label',
            ))
            ->add('email', EmailType::class, array(
                'label' => 'config.form_new_user.email_label',
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'config.form.save',
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wallabag\UserBundle\Entity\User',
        ));
    }

    public function getBlockPrefix()
    {
        return 'new_user';
    }
}
