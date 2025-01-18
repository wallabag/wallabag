<?php

namespace Wallabag\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class TaggingRuleImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'config.form_rules.file_label',
                'required' => true,
            ])
            ->add('import', SubmitType::class, [
                'label' => 'config.form_rules.import_submit',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'upload_tagging_rule_file';
    }
}
