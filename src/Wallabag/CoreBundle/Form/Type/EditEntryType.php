<?php

namespace Wallabag\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array('required' => true))
            ->add('is_public', 'checkbox', array('required' => false))
            // @todo: add autocomplete
            // ->add('tags', 'entity', array(
            //     'class' => 'Wallabag\CoreBundle\Entity\Tag',
            //     'choice_translation_domain' => true,
            // ))
            ->add('save', 'submit')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wallabag\CoreBundle\Entity\Entry',
        ));
    }

    public function getName()
    {
        return 'entry';
    }
}
