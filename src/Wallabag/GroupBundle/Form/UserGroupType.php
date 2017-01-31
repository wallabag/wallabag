<?php

namespace Wallabag\GroupBundle\Form\Type;

use Wallabag\GroupBundle\Entity\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserGroupType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('role', ChoiceType::class, [
                'label' => 'group.edit_user.role',
                'choices' => [
                    'group.roles.readonly' => Group::ROLE_READ_ONLY,
                    'group.roles.write' => Group::ROLE_WRITE,
                    'group.roles.manage_prez' => Group::ROLE_MANAGE_PREZ,
                    'group.roles.manage_users' => Group::ROLE_MANAGE_USERS,
                    'group.roles.admin' => Group::ROLE_ADMIN,
                ],
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
            'data_class' => 'Strut\StrutBundle\Entity\UserGroup',
        ));
    }
}
