<?php

namespace Wallabag\ImportBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class UploadImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, array(
                'label' => 'import.form.file_label',
            ))
            ->add('mark_as_read', CheckboxType::class, array(
                'label' => 'import.form.mark_as_read_label',
                'required' => false,
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'import.form.save_label',
            ))
        ;
    }

    public function getBlockPrefix()
    {
        return 'upload_import_file';
    }
}
