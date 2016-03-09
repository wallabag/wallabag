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
            ->add('rule', TextType::class, array(
                'required' => true,
                'label' => 'config.form_rules.rule_label',
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'config.form.save',
            ))
        ;

        $tagsField = $builder
            ->create('tags', TextType::class, array(
                'label' => 'config.form_rules.tags_label',
            ))
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
