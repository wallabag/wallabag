<?php

namespace Wallabag\FederationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wallabag\FederationBundle\Entity\Account;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextareaType::class, ['label' => 'config.form_account.description_label'])
            ->add('avatar', FileType::class, [
                'label' => 'config.form_account.avatar_label',
                'required' => false,
                'data_class' => null,
            ])
            ->add('banner', FileType::class, [
                'label' => 'config.form_account.banner_label',
                'required' => false,
                'data_class' => null,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'config.form.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Account::class,
        ));
    }

    public function getBlockPrefix()
    {
        return 'update_account';
    }
}
