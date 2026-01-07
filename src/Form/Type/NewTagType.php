<?php

namespace Wallabag\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wallabag\Entity\Tag;

class NewTagType extends AbstractType
{
    public const MAX_LENGTH = 40;
    public const MAX_TAGS = 5;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'tag.new.placeholder',
                    'max_length' => self::MAX_LENGTH,
                ],
            ])
            ->add('add', SubmitType::class, [
                'label' => 'tag.new.add',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tag::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'tag';
    }
}
