<?php

namespace Wallabag\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('feed_use_source', CheckboxType::class, [
                'label' => 'config.form_feed.feed_use_source',
                'property_path' => 'feedUseSource',
                'required' => false,
            ])
            ->add('feed_limit', null, [
                'label' => 'config.form_feed.feed_limit',
                'property_path' => 'feedLimit',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'config.form.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wallabag\CoreBundle\Entity\Config',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'feed_config';
    }
}
