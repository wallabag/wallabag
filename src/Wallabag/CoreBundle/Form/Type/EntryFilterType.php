<?php

namespace Wallabag\CoreBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\CheckboxFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\ChoiceFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\NumberRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EntryFilterType extends AbstractType
{
    private $user;
    private $repository;

    /**
     * Repository & user are used to get a list of language entries for this user.
     */
    public function __construct(EntityRepository $entryRepository, TokenStorageInterface $tokenStorage)
    {
        $this->repository = $entryRepository;

        $this->user = $tokenStorage->getToken() ? $tokenStorage->getToken()->getUser() : null;

        if (null === $this->user || !\is_object($this->user)) {
            return;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('readingTime', NumberRangeFilterType::class, [
                'left_number_options' => [
                    'condition_operator' => FilterOperands::OPERATOR_GREATER_THAN_EQUAL,
                    'attr' => ['min' => 0],
                ],
                'right_number_options' => [
                    'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL,
                    'attr' => ['min' => 0],
                ],
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    $lower = $values['value']['left_number'][0];
                    $upper = $values['value']['right_number'][0];

                    $min = (int) ($lower * $this->user->getConfig()->getReadingSpeed() / 200);
                    $max = (int) ($upper * $this->user->getConfig()->getReadingSpeed() / 200);

                    if (null === $lower && null === $upper) {
                        // no value? no filter
                        return;
                    } elseif (null === $lower && null !== $upper) {
                        // only lower value is defined: query all entries with reading LOWER THAN this value
                        $expression = $filterQuery->getExpr()->lte($field, $max);
                    } elseif (null !== $lower && null === $upper) {
                        // only upper value is defined: query all entries with reading GREATER THAN this value
                        $expression = $filterQuery->getExpr()->gte($field, $min);
                    } else {
                        // both value are defined, perform a between
                        $expression = $filterQuery->getExpr()->between($field, $min, $max);
                    }

                    return $filterQuery->createCondition($expression);
                },
                'label' => 'entry.filters.reading_time.label',
            ])
            ->add('createdAt', DateRangeFilterType::class, [
                'left_date_options' => [
                    'attr' => [
                        'placeholder' => 'yyyy-mm-dd',
                    ],
                    'format' => 'yyyy-MM-dd',
                    'widget' => 'single_text',
                ],
                'right_date_options' => [
                    'attr' => [
                        'placeholder' => 'yyyy-mm-dd',
                    ],
                    'format' => 'yyyy-MM-dd',
                    'widget' => 'single_text',
                ],
                'label' => 'entry.filters.created_at.label',
            ])
            ->add('domainName', TextFilterType::class, [
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    $value = $values['value'];
                    if (\strlen($value) <= 2 || empty($value)) {
                        return;
                    }
                    $expression = $filterQuery->getExpr()->like($field, $filterQuery->getExpr()->lower($filterQuery->getExpr()->literal('%' . $value . '%')));

                    return $filterQuery->createCondition($expression);
                },
                'label' => 'entry.filters.domain_label',
            ])
            ->add('httpStatus', TextFilterType::class, [
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    $value = $values['value'];
                    if (false === \array_key_exists($value, Response::$statusTexts)) {
                        return;
                    }

                    $paramName = sprintf('%s', str_replace('.', '_', $field));
                    $expression = $filterQuery->getExpr()->eq($field, ':' . $paramName);
                    $parameters = [$paramName => $value];

                    return $filterQuery->createCondition($expression, $parameters);
                },
                'label' => 'entry.filters.http_status_label',
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
            ->add('isPublic', CheckboxFilterType::class, [
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (false === $values['value']) {
                        return;
                    }

                    // is_public isn't a real field
                    // we should use the "uid" field to determine if the entry has been made public
                    $expression = $filterQuery->getExpr()->isNotNull($values['alias'] . '.uid');

                    return $filterQuery->createCondition($expression);
                },
                'label' => 'entry.filters.is_public_label',
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
