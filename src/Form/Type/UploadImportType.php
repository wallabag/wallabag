<?php

namespace Wallabag\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class UploadImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'import.form.file_label',
                'required' => true,
            ])
            ->add('mark_as_read', CheckboxType::class, [
                'label' => 'import.form.mark_as_read_label',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'import.form.save_label',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'upload_import_file';
    }
}
