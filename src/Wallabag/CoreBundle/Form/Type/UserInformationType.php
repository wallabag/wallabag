<?php

namespace Wallabag\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserInformationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'label' => 'config.form_user.name_label',
            ))
            ->add('email', EmailType::class, array(
                'label' => 'config.form_user.email_label',
            ))
            ->add('twoFactorAuthentication', CheckboxType::class, array(
                'required' => false,
                'label' => 'config.form_user.twoFactorAuthentication_label',
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'config.form.save',
            ))
            ->remove('username')
            ->remove('plainPassword')
        ;
    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wallabag\UserBundle\Entity\User',
        ));
    }

    public function getBlockPrefix()
    {
        return 'update_user';
    }
}
