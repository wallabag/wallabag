<?php

namespace Wallabag\CoreBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;

class EntryFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('readingTime', 'filter_number_range')
            ->add('createdAt', 'filter_date_range', array(
                    'left_date_options' => array(
                        'attr' => array(
                            'placeholder' => 'dd/mm/yyyy',
                        ),
                        'format' => 'dd/MM/yyyy',
                        'widget' => 'single_text',
                    ),
                    'right_date_options' => array(
                        'attr' => array(
                            'placeholder' => 'dd/mm/yyyy',
                        ),
                        'format' => 'dd/MM/yyyy',
                        'widget' => 'single_text',
                    ),
                )
            )
            ->add('domainName', 'filter_text', array(
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                        $value = $values['value'];
                        if (strlen($value) <= 3 || empty($value)) {
                            return;
                        }
                        $expression = $filterQuery->getExpr()->like($field, $filterQuery->getExpr()->literal('%'.$value.'%'));

                        return $filterQuery->createCondition($expression);
                    },
            ))
            ->add('isArchived', 'filter_checkbox')
            ->add('isStarred', 'filter_checkbox');
    }

    public function getName()
    {
        return 'entry_filter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'validation_groups' => array('filtering'),
        ));
    }
}
