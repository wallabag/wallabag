<?php

namespace Wallabag\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wallabag\Entity\Entry;

class NewEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', UrlType::class, [
                'required' => true,
                'label' => 'entry.new.form_new.url_label',
                'default_protocol' => null,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Entry::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'entry';
    }
}
