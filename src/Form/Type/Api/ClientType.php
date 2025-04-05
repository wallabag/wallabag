<?php

namespace Wallabag\Form\Type\Api;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wallabag\Entity\Api\Client;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'developer.client.form.name_label'])
            ->add('redirect_uris', UrlType::class, [
                'required' => false,
                'label' => 'developer.client.form.redirect_uris_label',
                'property_path' => 'redirectUris',
                'default_protocol' => null,
            ])
            ->add('save', SubmitType::class, ['label' => 'developer.client.form.save_label'])
        ;

        $builder->get('redirect_uris')
            ->addModelTransformer(new CallbackTransformer(
                fn ($originalUri) => $originalUri,
                fn ($submittedUri) => [$submittedUri]
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'client';
    }
}
