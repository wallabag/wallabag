<?php

namespace Wallabag\CoreBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntryFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('readingTime', 'filter_number_range')
            ->add('createdAt', 'filter_date_range', array(
                    'left_date_options' => array(
                        'attr' => array(
                            'placeholder' => 'dd/mm/yyyy'),
                        'format' => 'dd/MM/yyyy',
                        'widget' => 'single_text'
                    ),
                    'right_date_options' => array(
                        'attr' => array(
                            'placeholder' => 'dd/mm/yyyy'),
                        'format' => 'dd/MM/yyyy',
                        'widget' => 'single_text'
                    )));
    }

    public function getName()
    {
        return 'entry_filter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => false,
            'validation_groups' => array('filtering')
        ));
    }
}
