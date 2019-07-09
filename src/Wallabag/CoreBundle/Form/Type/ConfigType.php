<?php

namespace Wallabag\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wallabag\CoreBundle\Entity\Config;

class ConfigType extends AbstractType
{
    private $themes = [];
    private $languages = [];

    /**
     * @param array $themes    Themes come from the LiipThemeBundle (liip_theme.themes)
     * @param array $languages Languages come from configuration, array just code language as key and label as value
     */
    public function __construct($themes, $languages)
    {
        $this->themes = array_combine(
            $themes,
            array_map(function ($s) {
                return ucwords(strtolower(str_replace('-', ' ', $s)));
            }, $themes)
        );

        $this->languages = $languages;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('theme', ChoiceType::class, [
                'choices' => array_flip($this->themes),
                'label' => 'config.form_settings.theme_label',
            ])
            ->add('items_per_page', IntegerType::class, [
                'label' => 'config.form_settings.items_per_page_label',
                'property_path' => 'itemsPerPage',
            ])
            ->add('reading_speed', IntegerType::class, [
                'label' => 'config.form_settings.reading_speed.label',
                'property_path' => 'readingSpeed',
            ])
            ->add('action_mark_as_read', ChoiceType::class, [
                'label' => 'config.form_settings.action_mark_as_read.label',
                'property_path' => 'actionMarkAsRead',
                'choices' => [
                    'config.form_settings.action_mark_as_read.redirect_homepage' => Config::REDIRECT_TO_HOMEPAGE,
                    'config.form_settings.action_mark_as_read.redirect_current_page' => Config::REDIRECT_TO_CURRENT_PAGE,
                ],
            ])
            ->add('language', ChoiceType::class, [
                'choices' => array_flip($this->languages),
                'label' => 'config.form_settings.language_label',
            ])
            ->add('pocket_consumer_key', null, [
                'property_path' => 'pocketConsumerKey',
                'label' => 'config.form_settings.pocket_consumer_key_label',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'config.form.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wallabag\CoreBundle\Entity\Config',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'config';
    }
}
