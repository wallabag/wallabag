<?php

namespace Wallabag\ImportBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UploadImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', 'file')
            ->add('save', 'submit')
        ;
    }

    public function getName()
    {
        return 'upload_import_file';
    }
}
