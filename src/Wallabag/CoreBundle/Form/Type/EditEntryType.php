<?php

namespace Wallabag\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'entry.edit.title_label',
            ])
            ->add('url', UrlType::class, [
                'disabled' => true,
                'required' => false,
                'label' => 'entry.edit.url_label',
                'default_protocol' => null,
            ])
            ->add('origin_url', UrlType::class, [
                'required' => false,
                'property_path' => 'originUrl',
                'label' => 'entry.edit.origin_url_label',
                'default_protocol' => null,
            ])
            ->add('content', TextareaType::class, [
                'label' => 'entry.edit.content_label',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'entry.edit.save_label',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wallabag\CoreBundle\Entity\Entry',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'entry';
    }
}
