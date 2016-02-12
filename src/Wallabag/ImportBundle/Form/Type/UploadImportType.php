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
            ->add('file', FileType::class)
            ->add('mark_as_read', CheckboxType::class, array(
                    'label' => 'Mark all as read',
                    'required' => false,
                    ))
            ->add('save', SubmitType::class)
        ;
    }

    public function getBlockPrefix()
    {
        return 'upload_import_file';
    }
}
