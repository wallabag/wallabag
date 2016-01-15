<?php

namespace Wallabag\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wallabag\CoreBundle\Form\DataTransformer\StringToListTransformer;

class TaggingRuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rule', TextType::class, array('required' => true))
            ->add('save', SubmitType::class)
        ;

        $tagsField = $builder
            ->create('tags', TextType::class)
            ->addModelTransformer(new StringToListTransformer(','));

        $builder->add($tagsField);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wallabag\CoreBundle\Entity\TaggingRule',
        ));
    }

    public function getBlockPrefix()
    {
        return 'tagging_rule';
    }
}
