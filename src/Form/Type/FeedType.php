<?php

namespace Wallabag\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wallabag\Entity\Config;

class FeedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
            'data_class' => Config::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'feed_config';
    }
}
