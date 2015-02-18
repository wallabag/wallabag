<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://opensource.org/licenses/MIT see COPYING file
 */

class Language
{
    protected $wallabag;

    private $currentLanguage;

    private $languageNames = array(
        'cs_CZ.utf8' => 'čeština',
        'de_DE.utf8' => 'German',
        'en_GB.utf8' => 'English (GB)',
        'en_US.utf8' => 'English (US)',
        'es_ES.utf8' => 'Español',
        'fa_IR.utf8' => 'فارسی',
        'fr_FR.utf8' => 'Français',
        'it_IT.utf8' => 'Italiano',
        'pl_PL.utf8' => 'Polski',
        'pt_BR.utf8' => 'Português (Brasil)',
        'ru_RU.utf8' => 'Pусский',
        'sl_SI.utf8' => 'Slovenščina',
        'uk_UA.utf8' => 'Українська',
    );

    public function __construct(Poche $wallabag)
    {
        $this->wallabag = $wallabag;
        $pocheUser = Session::getParam('poche_user');
        $language  = (is_null($pocheUser) ? LANG : $pocheUser->getConfigValue('language'));

        @putenv('LC_ALL=' . $language);
        setlocale(LC_ALL, $language);
        bindtextdomain($language, LOCALE);
        textdomain($language);

        $this->currentLanguage = $language;
    }

    public function getLanguage() {
        return $this->currentLanguage;
    }

    public function getInstalledLanguages() {
        $handle = opendir(LOCALE);
        $languages = array();

        while (($language = readdir($handle)) !== false) {
            # Languages are stored in a directory, so all directory names are languages
            # @todo move language installation data to database
            if (! is_dir(LOCALE . '/' . $language) || in_array($language, array('..', '.', 'tools'))) {
                continue;
            }

            $current = false;

            if ($language === $this->getLanguage()) {
                $current = true;
            }

            $languages[] = array('name' => (isset($this->languageNames[$language]) ? $this->languageNames[$language] : $language), 'value' => $language, 'current' => $current);
        }

        return $languages;
    }


    /**
     * Update language for current user
     *
     * @param $newLanguage
     */
    public function updateLanguage($newLanguage)
    {
        # we are not going to change it to the current language
        if ($newLanguage == $this->getLanguage()) {
            $this->wallabag->messages->add('w', _('still using the "' . $this->getLanguage() . '" language!'));
            Tools::redirect('?view=config');
        }

        $languages = $this->getInstalledLanguages();
        $actualLanguage = false;

        foreach ($languages as $language) {
            if ($language['value'] == $newLanguage) {
                $actualLanguage = true;
                break;
            }
        }

        if (!$actualLanguage) {
            $this->wallabag->messages->add('e', _('that language does not seem to be installed'));
            Tools::redirect('?view=config');
        }

        $this->wallabag->store->updateUserConfig($this->wallabag->user->getId(), 'language', $newLanguage);
        $this->wallabag->messages->add('s', _('you have changed your language preferences'));

        $currentConfig = $_SESSION['poche_user']->config;
        $currentConfig['language'] = $newLanguage;

        $_SESSION['poche_user']->setConfig($currentConfig);

        Tools::emptyCache();
        Tools::redirect('?view=config');
    }
} 
