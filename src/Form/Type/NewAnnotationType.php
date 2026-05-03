<?php

namespace Wallabag\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wallabag\Entity\Annotation;

class NewAnnotationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text', null, [
                'empty_data' => '',
            ])
            ->add('quote', null, [
                'empty_data' => '',
                'trim' => false,
            ])
            ->add('ranges', CollectionType::class, [
                'entry_type' => RangeType::class,
                'allow_add' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Annotation::class,
        ]);
    }
}
