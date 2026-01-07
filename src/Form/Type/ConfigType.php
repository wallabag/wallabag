<?php

namespace Wallabag\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wallabag\Entity\Config;

class ConfigType extends AbstractType
{
    /**
     * @param array $languages Languages come from configuration, array just code language as key and label as value
     * @param array $fonts     Fonts come from configuration, array just font name as key / value
     */
    public function __construct(
        private $languages,
        private $fonts,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('font', ChoiceType::class, [
                'choices' => $this->initFonts(),
                'label' => 'config.form_settings.font_label',
                'property_path' => 'font',
            ])
            ->add('fontsize', RangeType::class, [
                'attr' => [
                    'min' => 0.6,
                    'max' => 2,
                    'step' => 0.1,
                ],
                'label' => 'config.form_settings.fontsize_label',
                'property_path' => 'fontsize',
            ])
            ->add('lineHeight', RangeType::class, [
                'attr' => [
                    'min' => 0.6,
                    'max' => 2,
                    'step' => 0.1,
                ],
                'label' => 'config.form_settings.lineheight_label',
                'property_path' => 'lineHeight',
            ])
            ->add('maxWidth', RangeType::class, [
                'attr' => [
                    'min' => 20,
                    'max' => 60,
                    'step' => 5,
                ],
                'label' => 'config.form_settings.maxwidth_label',
                'property_path' => 'maxWidth',
            ])
            ->add('items_per_page', IntegerType::class, [
                'label' => 'config.form_settings.items_per_page_label',
                'property_path' => 'itemsPerPage',
            ])
            ->add('display_thumbnails', CheckboxType::class, [
                'label' => 'config.form_settings.display_thumbnails_label',
                'property_path' => 'displayThumbnails',
                'required' => false,
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
            'data_class' => Config::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'config';
    }

    /**
     * Creates an array with font name as key / value.
     *
     * @return array
     */
    private function initFonts()
    {
        $fonts = [];

        foreach ($this->fonts as $font) {
            $fonts[$font] = $font;
        }

        return $fonts;
    }
}
