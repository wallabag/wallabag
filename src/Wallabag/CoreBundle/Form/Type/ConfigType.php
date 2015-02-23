<?php
namespace Wallabag\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('theme', 'choice', array(
                'choices' => array(
                    'baggy' => 'Baggy',
                    'courgette' => 'Courgette',
                    'dark' => 'Dark',
                    'default' => 'Default',
                    'dmagenta' => 'Dmagenta',
                    'solarized' => 'Solarized',
                    'solarized_dark' => 'Solarized Dark',
                ),
            ))
            ->add('items_per_page', 'text')
            ->add('language')
            ->add('save', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wallabag\CoreBundle\Entity\Config',
        ));
    }

    public function getName()
    {
        return 'config';
    }
}
