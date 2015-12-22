<?php

namespace Wallabag\CoreBundle\Filter;

use Doctrine\ORM\EntityRepository;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wallabag\UserBundle\Entity\User;

class EntryFilterType extends AbstractType
{
    private $user;
    private $repository;

    /**
     * Repository & user are used to get a list of language entries for this user.
     *
     * @param EntityRepository $entryRepository
     * @param User             $user
     */
    public function __construct(EntityRepository $entryRepository, User $user)
    {
        $this->repository = $entryRepository;
        $this->user = $user;
    }

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
                        if (strlen($value) <= 2 || empty($value)) {
                            return;
                        }
                        $expression = $filterQuery->getExpr()->like($field, $filterQuery->getExpr()->literal('%'.$value.'%'));

                        return $filterQuery->createCondition($expression);
                },
            ))
            ->add('isArchived', 'filter_checkbox')
            ->add('isStarred', 'filter_checkbox')
            ->add('previewPicture', 'filter_checkbox', array(
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (false === $values['value']) {
                        return;
                    }

                    $expression = $filterQuery->getExpr()->isNotNull($field);

                    return $filterQuery->createCondition($expression);
                },
            ))
            ->add('language', 'filter_choice', array(
                'choices' => $this->repository->findDistinctLanguageByUser($this->user->getId()),
            ))
        ;
    }

    public function getBlockPrefix()
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
