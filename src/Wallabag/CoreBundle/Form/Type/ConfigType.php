<?php
namespace Wallabag\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConfigType extends AbstractType
{
    private $themes = array();

    /**
     * @param array $themes Themes come from the LiipThemeBundle (liip_theme.themes)
     */
    public function __construct($themes)
    {
        $this->themes = array_combine(
            $themes,
            array_map(function ($s) { return ucwords(strtolower(str_replace('-', ' ', $s))); }, $themes)
        );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('theme', 'choice', array('choices' => $this->themes))
            ->add('items_per_page')
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
