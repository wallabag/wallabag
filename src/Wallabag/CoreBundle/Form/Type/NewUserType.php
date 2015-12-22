<?php

namespace Wallabag\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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
            ->add('username', TextType::class, array('required' => true))
            ->add('plainPassword', RepeatedType::class, array(
                'type' => 'password',
                'constraints' => array(
                    new Constraints\Length(array(
                        'min' => 8,
                        'minMessage' => 'Password should by at least 8 chars long',
                    )),
                    new Constraints\NotBlank(),
                ),
            ))
            ->add('email', EmailType::class)
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wallabag\UserBundle\Entity\User',
        ));
    }

    public function getBlockPrefix()
    {
        return 'new_user';
    }
}
