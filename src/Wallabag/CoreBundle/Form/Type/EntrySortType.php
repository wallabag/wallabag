<?php

namespace Wallabag\CoreBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\CheckboxFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\ChoiceFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EntrySortType extends AbstractType
{
    private $user;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->user = $tokenStorage->getToken() ? $tokenStorage->getToken()->getUser() : null;

        if (null === $this->user || !\is_object($this->user)) {
            return;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sortOrder', CheckboxFilterType::class)
            ->add('sortType', ChoiceFilterType::class, [
                'choices' => [
                    'createdAt' => 'createdAt',
                    'title' => 'title',
                    'updatedAt' => 'updatedAt',
                ],
                'label' => 'entry.sort.status_label',
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'entry_sort';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'validation_groups' => ['sortering'],
        ]);
    }
}
