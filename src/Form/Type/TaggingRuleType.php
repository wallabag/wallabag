<?php

namespace Wallabag\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wallabag\Entity\TaggingRule;
use Wallabag\Form\DataTransformer\StringToListTransformer;

class TaggingRuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rule', TextType::class, [
                'required' => true,
                'label' => 'config.form_rules.rule_label',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'config.form.save',
            ])
        ;

        $tagsField = $builder
            ->create('tags', TextType::class, [
                'label' => 'config.form_rules.tags_label',
            ])
            ->addModelTransformer(new StringToListTransformer(','));

        $builder->add($tagsField);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TaggingRule::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'tagging_rule';
    }
}
