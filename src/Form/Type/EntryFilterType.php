<?php

namespace Wallabag\Form\Type;

use Spiriit\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Spiriit\Bundle\FormFilterBundle\Filter\FilterOperands;
use Spiriit\Bundle\FormFilterBundle\Filter\Form\Type\CheckboxFilterType;
use Spiriit\Bundle\FormFilterBundle\Filter\Form\Type\ChoiceFilterType;
use Spiriit\Bundle\FormFilterBundle\Filter\Form\Type\DateRangeFilterType;
use Spiriit\Bundle\FormFilterBundle\Filter\Form\Type\NumberFilterType;
use Spiriit\Bundle\FormFilterBundle\Filter\Form\Type\NumberRangeFilterType;
use Spiriit\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Spiriit\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Wallabag\Entity\User;
use Wallabag\Repository\EntryRepository;

class EntryFilterType extends AbstractType
{
    /**
     * Repository & user are used to get a list of language entries for this user.
     */
    public function __construct(
        private readonly EntryRepository $repository,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        \assert($user instanceof User);

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
                'apply_filter' => static function (QueryInterface $filterQuery, $field, $values) use ($user) {
                    $lower = $values['value']['left_number'][0];
                    $upper = $values['value']['right_number'][0];

                    if (null === $lower && null === $upper) {
                        // no value? no filter
                        return;
                    }

                    \assert($filterQuery instanceof ORMQuery);

                    $min = (int) ($lower * $user->getConfig()->getReadingSpeed() / 200);
                    $max = (int) ($upper * $user->getConfig()->getReadingSpeed() / 200);

                    if (null === $lower && null !== $upper) {
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
                'apply_filter' => static function (QueryInterface $filterQuery, $field, $values) {
                    $value = $values['value'];
                    if (empty($value) || \strlen($value) <= 2) {
                        return false;
                    }

                    \assert($filterQuery instanceof ORMQuery);

                    $expression = $filterQuery->getExpr()->like($field, $filterQuery->getExpr()->lower($filterQuery->getExpr()->literal('%' . $value . '%')));

                    return $filterQuery->createCondition($expression);
                },
                'label' => 'entry.filters.domain_label',
                'attr' => [
                    'autocapitalize' => 'off',
                ],
            ])
            ->add('httpStatus', NumberFilterType::class, [
                'apply_filter' => static function (QueryInterface $filterQuery, $field, $values) {
                    $value = (int) $values['value'];
                    if (false === \array_key_exists($value, Response::$statusTexts)) {
                        return false;
                    }

                    \assert($filterQuery instanceof ORMQuery);

                    $paramName = \sprintf('%s', str_replace('.', '_', $field));
                    $expression = $filterQuery->getExpr()->eq($field, ':' . $paramName);
                    $parameters = [$paramName => $value];

                    return $filterQuery->createCondition($expression, $parameters);
                },
                'label' => 'entry.filters.http_status_label',
                'html5' => true,
                'attr' => [
                    'min' => 100,
                    'max' => 527,
                ],
            ])
            ->add('isArchived', CheckboxFilterType::class, [
                'label' => 'entry.filters.archived_label',
                'data' => $options['filter_archived'],
            ])
            ->add('isStarred', CheckboxFilterType::class, [
                'label' => 'entry.filters.starred_label',
                'data' => $options['filter_starred'],
            ])
            ->add('isUnread', CheckboxFilterType::class, [
                'label' => 'entry.filters.unread_label',
                'data' => $options['filter_unread'],
                'apply_filter' => static function (QueryInterface $filterQuery, $field, $values) {
                    if (false === $values['value']) {
                        return false;
                    }

                    \assert($filterQuery instanceof ORMQuery);

                    $expression = $filterQuery->getExpr()->eq('e.isArchived', 'false');

                    return $filterQuery->createCondition($expression);
                },
            ])
            ->add('isAnnotated', CheckboxFilterType::class, [
                'label' => 'entry.filters.annotated_label',
                'data' => $options['filter_annotated'],
                'apply_filter' => static function (QueryInterface $filterQuery, $field, $values) {
                    if (false === $values['value']) {
                        return false;
                    }

                    $qb = $filterQuery->getQueryBuilder();
                    $qb->innerJoin('e.annotations', 'a');
                },
            ])
            ->add('isNotParsed', CheckboxFilterType::class, [
                'label' => 'entry.filters.parsed_label',
                'data' => $options['filter_parsed'],
            ])
            ->add('previewPicture', CheckboxFilterType::class, [
                'apply_filter' => static function (QueryInterface $filterQuery, $field, $values) {
                    if (false === $values['value']) {
                        return false;
                    }

                    \assert($filterQuery instanceof ORMQuery);

                    $expression = $filterQuery->getExpr()->isNotNull($field);

                    return $filterQuery->createCondition($expression);
                },
                'label' => 'entry.filters.preview_picture_label',
            ])
            ->add('isPublic', CheckboxFilterType::class, [
                'apply_filter' => static function (QueryInterface $filterQuery, $field, $values) {
                    if (false === $values['value']) {
                        return false;
                    }

                    \assert($filterQuery instanceof ORMQuery);

                    // is_public isn't a real field
                    // we should use the "uid" field to determine if the entry has been made public
                    $expression = $filterQuery->getExpr()->isNotNull($values['alias'] . '.uid');

                    return $filterQuery->createCondition($expression);
                },
                'label' => 'entry.filters.is_public_label',
            ])
            ->add('language', ChoiceFilterType::class, [
                'choices' => array_flip($this->repository->findDistinctLanguageByUser($user->getId())),
                'label' => 'entry.filters.language_label',
            ])
            ->add('tags', TextFilterType::class, [
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    $arr = explode(',', $values['value']);
                    $inLabels = array_filter($arr, function($v) {
                        $label = trim($v);
                        return \strlen($label) > 2 && 0 !== \strpos($label, '-') && false === \strpos($label, '%');
                    });
                    $notInLabels = array_map(function($m) {
                        return ltrim($m, '-');
                    }, array_filter($arr, function($v) {
                        $label = trim($v);
                        return \strlen($label) > 2 && 0 === \strpos($label, '-') && false === \strpos($label, '%');
                    }));
                    $likeLabels = array_filter($arr, function($v) {
                        $label = trim($v);
                        return \strlen($label) > 2 && 0 !== \strpos($label, '-') && false !== \strpos($label, '%');
                    });
                    $notLikeLabels = array_map(function($m) {
                        return ltrim($m, '-');
                    }, array_filter($arr, function($v) {
                        $label = trim($v);
                        return \strlen($label) > 2 && 0 === \strpos($label, '-') && false !== \strpos($label, '%');
                    }));

                    if ([] === $inLabels && [] === $notInLabels && [] === $likeLabels && [] === $notLikeLabels) {
                        return;
                    }

                    \assert($filterQuery instanceof ORMQuery);

                    $filterQuery->getQueryBuilder()->leftJoin('e.tags', 't');
                    $andExpr = $filterQuery->getExpr()->andX();
                    $orExpr = $filterQuery->getExpr()->orX();
                    $andNotExpr = $filterQuery->getExpr()->andX();

                    if ([] !== $inLabels) {
                        $orExpr->add($filterQuery->getExpr()->in('t.label', ':inLabels'));
                        $filterQuery->getQueryBuilder()->setParameter(':inLabels', $inLabels);
                    }

                    if ([] !== $likeLabels) {
                        foreach ($likeLabels as $k => $label) {
                            $orExpr->add($filterQuery->getExpr()->like('t.label', ':likeLabel' . $k));
                            $filterQuery->getQueryBuilder()->setParameter(':likeLabel' . $k, $label);
                        }
                    }

                    if ([] !== $notInLabels) {
                        $sub = $this->repository->createQueryBuilder('e0')->select('e0.id')->leftJoin('e0.tags', 't0')->andWhere('e0.user = :userId')->andWhere('t0.label IN (:notInLabels)');
                        $andNotExpr->add($filterQuery->getExpr()->notIn('e.id', $sub->getDQL()));
                        $filterQuery->getQueryBuilder()->setParameter(':notInLabels', $notInLabels);
                    }

                    if ([] !== $notLikeLabels) {
                        $orSubExpr = $filterQuery->getExpr()->orX();
                        $sub = $this->repository->createQueryBuilder("e1")->select("e1.id")->leftJoin("e1.tags", "t1")->andWhere("e1.user = :userId");
                        foreach ($notLikeLabels as $k => $label) {
                            $orSubExpr->add($filterQuery->getExpr()->like('t1.label', ":notLikeLabel$k"));
                            $filterQuery->getQueryBuilder()->setParameter(":notLikeLabel$k", $label);
                        }
                        $sub->andWhere($orSubExpr);
                        $andNotExpr->add($filterQuery->getExpr()->notIn('e.id', $sub->getDQL()));
                    }

                    $andExpr->add($orExpr);
                    $andExpr->add($andNotExpr);

                    return $filterQuery->createCondition($andExpr);
                },
                'label' => 'entry.filters.tags_label',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'entry_filter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'validation_groups' => ['filtering'],
            'filter_archived' => false,
            'filter_starred' => false,
            'filter_unread' => false,
            'filter_annotated' => false,
            'filter_parsed' => false,
        ]);
    }
}
