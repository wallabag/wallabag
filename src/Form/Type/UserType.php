<?php

namespace Wallabag\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wallabag\Entity\User;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'label' => 'user.form.name_label',
            ])
            ->add('username', TextType::class, [
                'required' => true,
                'label' => 'user.form.username_label',
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'user.form.email_label',
            ])
            ->add('enabled', CheckboxType::class, [
                'required' => false,
                'label' => 'user.form.enabled_label',
            ])
            ->add('emailTwoFactor', CheckboxType::class, [
                'required' => false,
                'label' => 'user.form.twofactor_email_label',
            ])
            ->add('googleTwoFactor', CheckboxType::class, [
                'required' => false,
                'label' => 'user.form.twofactor_google_label',
                'mapped' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'user.form.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
