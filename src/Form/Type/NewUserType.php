<?php

namespace Wallabag\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Wallabag\Entity\User;

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
                    new Length([
                        'min' => 8,
                        'minMessage' => 'validator.password_too_short',
                    ]),
                    new NotBlank(),
                ],
                'label' => 'user.form.plain_password_label',
            ])
            ->add('email', EmailType::class, [
                'label' => 'user.form.email_label',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'user.form.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'new_user';
    }
}
