<?php

namespace Wallabag\GroupBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class GroupType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'label' => 'group.form.name_label',
            ])
            ->add('users', EntityType::class, array(
                'class' => 'WallabagUserBundle:User',
                'choice_label' => 'username',
                'multiple' => true,
                'expanded' => true,
            ))
            ->add('save', SubmitType::class, [
                'label' => 'group.form.save',
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wallabag\GroupBundle\Entity\Group',
        ));
    }
}
