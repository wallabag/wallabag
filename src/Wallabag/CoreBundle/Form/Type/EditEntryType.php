<?php

namespace Wallabag\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            ->add('is_public', CheckboxType::class, [
                'required' => false,
                'label' => 'entry.edit.is_public_label',
                'property_path' => 'isPublic',
            ])
            ->add('url', TextType::class, [
                'disabled' => true,
                'required' => false,
                'label' => 'entry.edit.url_label',
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
