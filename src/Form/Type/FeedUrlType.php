<?php

namespace Wallabag\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wallabag\Entity\FeedUrl;

class FeedUrlType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'entry.edit.title_label',
                'empty_data' => '',
            ])
            ->add('url', UrlType::class, [
                'label' => 'entry.new.form_new.url_label',
                'default_protocol' => null,
                'empty_data' => '',
            ])
            ->add('save', SubmitType::class, ['label' => 'config.form.save'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => FeedUrl::class]);
    }

    public function getBlockPrefix(): string
    {
        return 'feed';
    }
}
