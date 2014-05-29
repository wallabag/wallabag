<?php
/**
 * Part of Text_LanguageDetect
 *
 * PHP version 5
 *
 * @category  Text
 * @package   Text_LanguageDetect
 * @author    Christian Weiske <cweiske@php.net>
 * @copyright 2011 Christian Weiske <cweiske@php.net>
 * @license   http://www.debian.org/misc/bsd.license BSD
 * @version   SVN: $Id$
 * @link      http://pear.php.net/package/Text_LanguageDetect/
 */

/**
 * Provides a mapping between the languages from lang.dat and the
 * ISO 639-1 and ISO-639-2 codes.
 *
 * Note that this class contains only languages that exist in lang.dat.
 *
 * @category  Text
 * @package   Text_LanguageDetect
 * @author    Christian Weiske <cweiske@php.net>
 * @copyright 2011 Christian Weiske <cweiske@php.net>
 * @license   http://www.debian.org/misc/bsd.license BSD
 * @link      http://www.loc.gov/standards/iso639-2/php/code_list.php
 */
class Text_LanguageDetect_ISO639
{
    /**
     * Maps all language names from the language database to the
     * ISO 639-1 2-letter language code.
     *
     * NULL indicates that there is no 2-letter code.
     *
     * @var array
     */
    public static $nameToCode2 = array(
        'albanian'   => 'sq',
        'arabic'     => 'ar',
        'azeri'      => 'az',
        'bengali'    => 'bn',
        'bulgarian'  => 'bg',
        'cebuano'    => null,
        'croatian'   => 'hr',
        'czech'      => 'cs',
        'danish'     => 'da',
        'dutch'      => 'nl',
        'english'    => 'en',
        'estonian'   => 'et',
        'farsi'      => 'fa',
        'finnish'    => 'fi',
        'french'     => 'fr',
        'german'     => 'de',
        'hausa'      => 'ha',
        'hawaiian'   => null,
        'hindi'      => 'hi',
        'hungarian'  => 'hu',
        'icelandic'  => 'is',
        'indonesian' => 'id',
        'italian'    => 'it',
        'kazakh'     => 'kk',
        'kyrgyz'     => 'ky',
        'latin'      => 'la',
        'latvian'    => 'lv',
        'lithuanian' => 'lt',
        'macedonian' => 'mk',
        'mongolian'  => 'mn',
        'nepali'     => 'ne',
        'norwegian'  => 'no',
        'pashto'     => 'ps',
        'pidgin'     => null,
        'polish'     => 'pl',
        'portuguese' => 'pt',
        'romanian'   => 'ro',
        'russian'    => 'ru',
        'serbian'    => 'sr',
        'slovak'     => 'sk',
        'slovene'    => 'sl',
        'somali'     => 'so',
        'spanish'    => 'es',
        'swahili'    => 'sw',
        'swedish'    => 'sv',
        'tagalog'    => 'tl',
        'turkish'    => 'tr',
        'ukrainian'  => 'uk',
        'urdu'       => 'ur',
        'uzbek'      => 'uz',
        'vietnamese' => 'vi',
        'welsh'      => 'cy',
    );

    /**
     * Maps all language names from the language database to the
     * ISO 639-2 3-letter language code.
     *
     * @var array
     */
    public static $nameToCode3 = array(
        'albanian'   => 'sqi',
        'arabic'     => 'ara',
        'azeri'      => 'aze',
        'bengali'    => 'ben',
        'bulgarian'  => 'bul',
        'cebuano'    => 'ceb',
        'croatian'   => 'hrv',
        'czech'      => 'ces',
        'danish'     => 'dan',
        'dutch'      => 'nld',
        'english'    => 'eng',
        'estonian'   => 'est',
        'farsi'      => 'fas',
        'finnish'    => 'fin',
        'french'     => 'fra',
        'german'     => 'deu',
        'hausa'      => 'hau',
        'hawaiian'   => 'haw',
        'hindi'      => 'hin',
        'hungarian'  => 'hun',
        'icelandic'  => 'isl',
        'indonesian' => 'ind',
        'italian'    => 'ita',
        'kazakh'     => 'kaz',
        'kyrgyz'     => 'kir',
        'latin'      => 'lat',
        'latvian'    => 'lav',
        'lithuanian' => 'lit',
        'macedonian' => 'mkd',
        'mongolian'  => 'mon',
        'nepali'     => 'nep',
        'norwegian'  => 'nor',
        'pashto'     => 'pus',
        'pidgin'     => 'crp',
        'polish'     => 'pol',
        'portuguese' => 'por',
        'romanian'   => 'ron',
        'russian'    => 'rus',
        'serbian'    => 'srp',
        'slovak'     => 'slk',
        'slovene'    => 'slv',
        'somali'     => 'som',
        'spanish'    => 'spa',
        'swahili'    => 'swa',
        'swedish'    => 'swe',
        'tagalog'    => 'tgl',
        'turkish'    => 'tur',
        'ukrainian'  => 'ukr',
        'urdu'       => 'urd',
        'uzbek'      => 'uzb',
        'vietnamese' => 'vie',
        'welsh'      => 'cym',
    );

    /**
     * Maps ISO 639-1 2-letter language codes to the language names
     * in the language database
     *
     * Not all languages have a 2 letter code, so some are missing
     *
     * @var array
     */
    public static $code2ToName = array(
        'ar' => 'arabic',
        'az' => 'azeri',
        'bg' => 'bulgarian',
        'bn' => 'bengali',
        'cs' => 'czech',
        'cy' => 'welsh',
        'da' => 'danish',
        'de' => 'german',
        'en' => 'english',
        'es' => 'spanish',
        'et' => 'estonian',
        'fa' => 'farsi',
        'fi' => 'finnish',
        'fr' => 'french',
        'ha' => 'hausa',
        'hi' => 'hindi',
        'hr' => 'croatian',
        'hu' => 'hungarian',
        'id' => 'indonesian',
        'is' => 'icelandic',
        'it' => 'italian',
        'kk' => 'kazakh',
        'ky' => 'kyrgyz',
        'la' => 'latin',
        'lt' => 'lithuanian',
        'lv' => 'latvian',
        'mk' => 'macedonian',
        'mn' => 'mongolian',
        'ne' => 'nepali',
        'nl' => 'dutch',
        'no' => 'norwegian',
        'pl' => 'polish',
        'ps' => 'pashto',
        'pt' => 'portuguese',
        'ro' => 'romanian',
        'ru' => 'russian',
        'sk' => 'slovak',
        'sl' => 'slovene',
        'so' => 'somali',
        'sq' => 'albanian',
        'sr' => 'serbian',
        'sv' => 'swedish',
        'sw' => 'swahili',
        'tl' => 'tagalog',
        'tr' => 'turkish',
        'uk' => 'ukrainian',
        'ur' => 'urdu',
        'uz' => 'uzbek',
        'vi' => 'vietnamese',
    );

    /**
     * Maps ISO 639-2 3-letter language codes to the language names
     * in the language database.
     *
     * @var array
     */
    public static $code3ToName = array(
        'ara' => 'arabic',
        'aze' => 'azeri',
        'ben' => 'bengali',
        'bul' => 'bulgarian',
        'ceb' => 'cebuano',
        'ces' => 'czech',
        'crp' => 'pidgin',
        'cym' => 'welsh',
        'dan' => 'danish',
        'deu' => 'german',
        'eng' => 'english',
        'est' => 'estonian',
        'fas' => 'farsi',
        'fin' => 'finnish',
        'fra' => 'french',
        'hau' => 'hausa',
        'haw' => 'hawaiian',
        'hin' => 'hindi',
        'hrv' => 'croatian',
        'hun' => 'hungarian',
        'ind' => 'indonesian',
        'isl' => 'icelandic',
        'ita' => 'italian',
        'kaz' => 'kazakh',
        'kir' => 'kyrgyz',
        'lat' => 'latin',
        'lav' => 'latvian',
        'lit' => 'lithuanian',
        'mkd' => 'macedonian',
        'mon' => 'mongolian',
        'nep' => 'nepali',
        'nld' => 'dutch',
        'nor' => 'norwegian',
        'pol' => 'polish',
        'por' => 'portuguese',
        'pus' => 'pashto',
        'rom' => 'romanian',
        'rus' => 'russian',
        'slk' => 'slovak',
        'slv' => 'slovene',
        'som' => 'somali',
        'spa' => 'spanish',
        'sqi' => 'albanian',
        'srp' => 'serbian',
        'swa' => 'swahili',
        'swe' => 'swedish',
        'tgl' => 'tagalog',
        'tur' => 'turkish',
        'ukr' => 'ukrainian',
        'urd' => 'urdu',
        'uzb' => 'uzbek',
        'vie' => 'vietnamese',
    );

    /**
     * Returns the 2-letter ISO 639-1 code for the given language name.
     *
     * @param string $lang English language name like "swedish"
     *
     * @return string Two-letter language code (e.g. "sv") or NULL if not found
     */
    public static function nameToCode2($lang)
    {
        $lang = strtolower($lang);
        if (!isset(self::$nameToCode2[$lang])) {
            return null;
        }
        return self::$nameToCode2[$lang];
    }

    /**
     * Returns the 3-letter ISO 639-2 code for the given language name.
     *
     * @param string $lang English language name like "swedish"
     *
     * @return string Three-letter language code (e.g. "swe") or NULL if not found
     */
    public static function nameToCode3($lang)
    {
        $lang = strtolower($lang);
        if (!isset(self::$nameToCode3[$lang])) {
            return null;
        }
        return self::$nameToCode3[$lang];
    }

    /**
     * Returns the language name for the given 2-letter ISO 639-1 code.
     *
     * @param string $code Two-letter language code (e.g. "sv")
     *
     * @return string English language name like "swedish"
     */
    public static function code2ToName($code)
    {
        $lang = strtolower($code);
        if (!isset(self::$code2ToName[$code])) {
            return null;
        }
        return self::$code2ToName[$code];
    }

    /**
     * Returns the language name for the given 3-letter ISO 639-2 code.
     *
     * @param string $code Three-letter language code (e.g. "swe")
     *
     * @return string English language name like "swedish"
     */
    public static function code3ToName($code)
    {
        $lang = strtolower($code);
        if (!isset(self::$code3ToName[$code])) {
            return null;
        }
        return self::$code3ToName[$code];
    }
}