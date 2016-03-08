<?php

namespace Wallabag\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('redirect_uris', UrlType::class, array('required' => true, 'label' => 'Redirect URIs'))
            ->add('save', SubmitType::class, array('label' => 'Create a new client'))
        ;

        $builder->get('redirect_uris')
            ->addModelTransformer(new CallbackTransformer(
                function ($originalUri) {
                    return $originalUri;
                },
                function ($submittedUri) {
                    return array($submittedUri);
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wallabag\ApiBundle\Entity\Client',
        ));
    }

    public function getBlockPrefix()
    {
        return 'client';
    }
}
