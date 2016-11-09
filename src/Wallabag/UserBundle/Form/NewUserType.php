<?php

namespace Wallabag\UserBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class NewUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'required' => true,
                'label' => 'user.form.username_label',
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'validator.password_must_match',
                'first_options' => ['label' => 'user.form.password_label'],
                'second_options' => ['label' => 'user.form.repeat_new_password_label'],
                'constraints' => [
                    new Constraints\Length([
                        'min' => 8,
                        'minMessage' => 'validator.password_too_short',
                    ]),
                    new Constraints\NotBlank(),
                ],
                'label' => 'user.form.plain_password_label',
            ])
            ->add('email', EmailType::class, [
                'label' => 'user.form.email_label',
            ])
            ->add('groups', EntityType::class, array(
                'class' => 'WallabagGroupBundle:Group',
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
            ))
            ->add('save', SubmitType::class, [
                'label' => 'user.form.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wallabag\UserBundle\Entity\User',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'new_user';
    }
}
