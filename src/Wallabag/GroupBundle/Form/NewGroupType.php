<?php

namespace Wallabag\GroupBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wallabag\GroupBundle\Entity\Group;

class NewGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'group.form.name_label',
            ])
            ->add('defaultRole', ChoiceType::class, [
                'label' => 'group.form.role_label',
                'choices' => [
                    'group.roles.readonly' => Group::ROLE_READ_ONLY,
                    'group.roles.write' => Group::ROLE_WRITE,
                    'group.roles.manage_entries' => Group::ROLE_MANAGE_ENTRIES,
                    'group.roles.manage_users' => Group::ROLE_MANAGE_USERS,
                    'group.roles.admin' => Group::ROLE_ADMIN,
                ],
            ])
            ->add('acceptSystem', ChoiceType::class, [
                'label' => 'group.form.access_label',
                'choices' => [
                    'group.access.open' => Group::ACCESS_OPEN,
                    'group.access.request' => Group::ACCESS_REQUEST,
                    'group.access.password' => Group::ACCESS_PASSWORD,
                    'group.access.invitation' => Group::ACCESS_INVITATION_ONLY,
                    'group.access.hidden' => Group::ACCESS_HIDDEN,
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'group.form.password_label',
                'required' => false,
                'attr' => ['autocomplete' => 'off'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'group.form.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wallabag\GroupBundle\Entity\Group',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'new_group';
    }
}
