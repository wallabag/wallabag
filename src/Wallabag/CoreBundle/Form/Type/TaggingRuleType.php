<?php

namespace Wallabag\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wallabag\CoreBundle\Form\DataTransformer\StringToListTransformer;

class TaggingRuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rule', 'text', array('required' => true))
            ->add('save', 'submit')
        ;

        $tagsField = $builder
            ->create('tags', 'text')
            ->addModelTransformer(new StringToListTransformer(','));

        $builder->add($tagsField);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wallabag\CoreBundle\Entity\TaggingRule',
        ));
    }

    public function getName()
    {
        return 'tagging_rule';
    }
}
