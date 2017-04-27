<?php

namespace Wallabag\CoreBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\GroupBundle\Entity\Group;

class EditGroupSharesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('groupshares', EntityType::class, [
                'required' => true,
                'class' => Group::class,
                'choices' => $options['groups'],
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Share',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Entry::class,
        ]);
        $resolver->setRequired('groups');
    }

    public function getBlockPrefix()
    {
        return 'group_shares';
    }
}
