<?php

namespace Wallabag\UserBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class UserType extends AbstractType
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
                'label' => 'user.form.name_label',
            ])
            ->add('username', TextType::class, [
                'required' => true,
                'label' => 'user.form.username_label',
            ])
            ->add('groups', EntityType::class, array(
                'class' => 'WallabagGroupBundle:Group',
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
            ))
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'user.form.email_label',
            ])
            ->add('enabled', CheckboxType::class, [
                'required' => false,
                'label' => 'user.form.enabled_label',
            ])
            ->add('twoFactorAuthentication', CheckboxType::class, [
                'required' => false,
                'label' => 'user.form.twofactor_label',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'user.form.save',
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wallabag\UserBundle\Entity\User',
        ));
    }
}
