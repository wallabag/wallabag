<?php

namespace Wallabag\CoreBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\NumberRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\CheckboxFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\ChoiceFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class EntryFilterType extends AbstractType
{
    private $user;
    private $repository;

    /**
     * Repository & user are used to get a list of language entries for this user.
     *
     * @param EntityRepository $entryRepository
     * @param TokenStorage     $token
     */
    public function __construct(EntityRepository $entryRepository, TokenStorage $token)
    {
        $this->repository = $entryRepository;
        $this->user = $token->getToken()->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('readingTime', NumberRangeFilterType::class, [
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    $value = $values['value'];

                    if (null === $value['left_number'][0] || null === $value['right_number'][0]) {
                        return;
                    }

                    $min = (int) ($value['left_number'][0] * $this->user->getConfig()->getReadingSpeed());
                    $max = (int) ($value['right_number'][0] * $this->user->getConfig()->getReadingSpeed());

                    $expression = $filterQuery->getExpr()->between($field, $min, $max);

                    return $filterQuery->createCondition($expression);
                },
                'label' => 'entry.filters.reading_time.label',
            ])
            ->add('createdAt', DateRangeFilterType::class, [
                    'left_date_options' => [
                        'attr' => [
                            'placeholder' => 'dd/mm/yyyy',
                        ],
                        'format' => 'dd/MM/yyyy',
                        'widget' => 'single_text',
                    ],
                    'right_date_options' => [
                        'attr' => [
                            'placeholder' => 'dd/mm/yyyy',
                        ],
                        'format' => 'dd/MM/yyyy',
                        'widget' => 'single_text',
                    ],
                    'label' => 'entry.filters.created_at.label',
                ]
            )
            ->add('domainName', TextFilterType::class, [
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    $value = $values['value'];
                    if (strlen($value) <= 2 || empty($value)) {
                        return;
                    }
                    $expression = $filterQuery->getExpr()->like($field, $filterQuery->getExpr()->literal('%'.$value.'%'));

                    return $filterQuery->createCondition($expression);
                },
                'label' => 'entry.filters.domain_label',
            ])
            ->add('isArchived', CheckboxFilterType::class, [
                'label' => 'entry.filters.archived_label',
            ])
            ->add('isStarred', CheckboxFilterType::class, [
                'label' => 'entry.filters.starred_label',
            ])
            ->add('isUnread', CheckboxFilterType::class, [
                'label' => 'entry.filters.unread_label',
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (false === $values['value']) {
                        return;
                    }

                    $expression = $filterQuery->getExpr()->eq('e.isArchived', 'false');

                    return $filterQuery->createCondition($expression);
                },
            ])
            ->add('previewPicture', CheckboxFilterType::class, [
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (false === $values['value']) {
                        return;
                    }

                    $expression = $filterQuery->getExpr()->isNotNull($field);

                    return $filterQuery->createCondition($expression);
                },
                'label' => 'entry.filters.preview_picture_label',
            ])
            ->add('language', ChoiceFilterType::class, [
                'choices' => array_flip($this->repository->findDistinctLanguageByUser($this->user->getId())),
                'label' => 'entry.filters.language_label',
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'entry_filter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'validation_groups' => ['filtering'],
        ]);
    }
}
