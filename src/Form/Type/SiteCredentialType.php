<?php

namespace Wallabag\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wallabag\Entity\SiteCredential;

class SiteCredentialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('host', TextType::class, [
                'label' => 'site_credential.form.host_label',
            ])
            ->add('username', TextType::class, [
                'label' => 'site_credential.form.username_label',
                'data' => '',
            ])
            ->add('password', PasswordType::class, [
                'label' => 'site_credential.form.password_label',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'config.form.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SiteCredential::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'site_credential';
    }
}
