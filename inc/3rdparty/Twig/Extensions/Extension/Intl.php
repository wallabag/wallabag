<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Twig_Extensions_Extension_Intl extends Twig_Extension
{
    public function __construct()
    {
        if (!class_exists('IntlDateFormatter')) {
            throw new RuntimeException('The intl extension is needed to use intl-based filters.');
        }
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return array(
            'localizeddate' => new Twig_Filter_Function('twig_localized_date_filter', array('needs_environment' => true)),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'intl';
    }
}

function twig_localized_date_filter(Twig_Environment $env, $date, $dateFormat = 'medium', $timeFormat = 'medium', $locale = null, $timezone = null, $format = null)
{
    $date = twig_date_converter($env, $date, $timezone);

    $formatValues = array(
        'none'   => IntlDateFormatter::NONE,
        'short'  => IntlDateFormatter::SHORT,
        'medium' => IntlDateFormatter::MEDIUM,
        'long'   => IntlDateFormatter::LONG,
        'full'   => IntlDateFormatter::FULL,
    );

    $formatter = IntlDateFormatter::create(
        $locale !== null ? $locale : Locale::getDefault(),
        $formatValues[$dateFormat],
        $formatValues[$timeFormat],
        $date->getTimezone()->getName(),
        IntlDateFormatter::GREGORIAN,
        $format
    );

    return $formatter->format($date->getTimestamp());
}
