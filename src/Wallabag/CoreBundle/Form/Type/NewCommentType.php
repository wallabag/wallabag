<?php

namespace Wallabag\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewCommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', 'textarea', array('required' => true,
                'attr' => array('class' => 'materialize-textarea'),
                ))
            ->add('dom', 'hidden')
            ->add('save', 'submit')
            ->add('reset', 'reset')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wallabag\CoreBundle\Entity\Comment',
        ));
    }

    public function getName()
    {
        return 'comment';
    }
}
