<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://opensource.org/licenses/MIT see COPYING file
 */

class Template extends Twig_Environment
{
    protected $wallabag;

    private $canRenderTemplates = TRUE;
    private $currentTheme = '';

    public function __construct(Poche $wallabag)
    {
        $this->wallabag = $wallabag;

        // Set up theme
        $pocheUser = Session::getParam('poche_user');

        $themeDirectory = (is_null($pocheUser) ? DEFAULT_THEME : $pocheUser->getConfigValue('theme'));

        if ($themeDirectory === false || !is_dir(THEME . '/' . $themeDirectory)) {
            $themeDirectory = DEFAULT_THEME;
        }

        $this->currentTheme = $themeDirectory;

        if ($this->_themeIsInstalled() === array()) {
            $this->_init();
        }
    }

    /**
     * Returns true if selected theme is installed
     *
     * @return bool
     */
    private function _themeIsInstalled()
    {
        $errors = array();

        // Twig is an absolute requirement for wallabag to function.
        // Abort immediately if the Composer installer hasn't been run yet
        if (!$this->canRenderTemplates) {
            $errors[]   = 'Twig does not seem to be installed. Please initialize the Composer installation to automatically fetch dependencies. You can also download <a href="http://wllbg.org/vendor">vendor.zip</a> and extract it in your wallabag folder.';
        }

        // Check if the selected theme and its requirements are present
        $theme = $this->getTheme();
        if ($theme != '' && !is_dir(THEME . '/' . $theme)) {
            $errors[]                   = 'The currently selected theme (' . $theme . ') does not seem to be properly installed (Missing directory: ' . THEME . '/' . $theme . ')';
            $this->canRenderTemplates   = FALSE;
        }

        $themeInfo = $this->getThemeInfo($theme);
        if (isset($themeInfo['requirements']) && is_array($themeInfo['requirements'])) {
            foreach ($themeInfo['requirements'] as $requiredTheme) {
                if (! is_dir(THEME . '/' . $requiredTheme)) {
                    $errors[]                   = 'The required "' . $requiredTheme . '" theme is missing for the current theme (' . $theme . ')';
                    $this->canRenderTemplates   = FALSE;
                }
            }
        }

        $currentErrors = (is_null(Session::getParam('errors'))? array() : Session::getParam('errors'));
        Session::setParam('errors', array_merge($errors, $currentErrors));

        return $errors;
    }

    /**
     * Initialization for templates
     */
    private function _init()
    {
        $loaderChain    = new Twig_Loader_Chain();
        $theme          = $this->getTheme();

        // add the current theme as first to the loader chain
        // so Twig will look there first for overridden template files
        try {
            $loaderChain->addLoader(new Twig_Loader_Filesystem(THEME . '/' . $theme));
        } catch (Twig_Error_Loader $e) {
            # @todo isInstalled() should catch this, inject Twig later
            die('The currently selected theme (' . $theme . ') does not seem to be properly installed (' . THEME . '/' . $theme .' is missing)');
        }

        // add all required themes to the loader chain
        $themeInfo = $this->getThemeInfo($theme);
        if (isset($themeInfo['requirements']) && is_array($themeInfo['requirements'])) {
            foreach ($themeInfo['requirements'] as $requiredTheme) {
                try {
                    $loaderChain->addLoader(new Twig_Loader_Filesystem(THEME . '/' . $requiredTheme));
                } catch (Twig_Error_Loader $e) {
                    # @todo isInstalled() should catch this, inject Twig later
                    die('The required "' . $requiredTheme . '" theme is missing for the current theme (' . $theme . ')');
                }
            }
        }

        if (DEBUG_POCHE) {
            $twigParams = array();
        } else {
            $twigParams = array('cache' => CACHE);
        }

        parent::__construct($loaderChain, $twigParams);

        //$tpl = new Twig_Environment($loaderChain, $twigParams);
        $this->addExtension(new Twig_Extensions_Extension_I18n());

        # filter to display domain name of an url
        $filter = new Twig_SimpleFilter('getDomain', 'Tools::getDomain');
        $this->addFilter($filter);

        # filter for reading time
        $filter = new Twig_SimpleFilter('getReadingTime', 'Tools::getReadingTime');
        $this->addFilter($filter);
    }

    /**
     * Returns current theme
     *
     * @return string
     */
    public function getTheme()
    {
        return $this->currentTheme;
    }

    /**
     * Provides theme information by parsing theme.ini file if present in the theme's root directory.
     * In all cases, the following data will be returned:
     * - name: theme's name, or key if the theme is unnamed,
     * - current: boolean informing if the theme is the current user theme.
     *
     * @param string $theme Theme key (directory name)
     * @return array|boolean Theme information, or false if the theme doesn't exist.
     */
    public function getThemeInfo($theme)
    {
        if (!is_dir(THEME . '/' . $theme)) {
            return false;
        }

        $themeIniFile   = THEME . '/' . $theme . '/theme.ini';
        $themeInfo      = array();

        if (is_file($themeIniFile) && is_readable($themeIniFile)) {
            $themeInfo = parse_ini_file($themeIniFile);
        }

        if ($themeInfo === false) {
            $themeInfo = array();
        }

        if (!isset($themeInfo['name'])) {
            $themeInfo['name'] = $theme;
        }

        $themeInfo['current'] = ($theme === $this->getTheme());

        return $themeInfo;
    }

    /**
     * Returns an array with installed themes
     *
     * @return array
     */
    public function getInstalledThemes()
    {
        $handle = opendir(THEME);
        $themes = array();

        while (($theme = readdir($handle)) !== false) {
            # Themes are stored in a directory, so all directory names are themes
            # @todo move theme installation data to database
            if (!is_dir(THEME . '/' . $theme) || in_array($theme, array('.', '..', '_global'))) {
                continue;
            }

            $themes[$theme] = $this->getThemeInfo($theme);
        }

        ksort($themes);

        return $themes;
    }

    /**
     * Update theme for the current user
     *
     * @param $newTheme
     */
    public function updateTheme($newTheme)
    {
        # we are not going to change it to the current theme...
        if ($newTheme == $this->getTheme()) {
            $this->wallabag->messages->add('w', _('still using the "' . $this->getTheme() . '" theme!'));
            Tools::redirect('?view=config');
        }

        $themes = $this->getInstalledThemes();
        $actualTheme = false;

        foreach (array_keys($themes) as $theme) {
            if ($theme == $newTheme) {
                $actualTheme = true;
                break;
            }
        }

        if (!$actualTheme) {
            $this->wallabag->messages->add('e', _('that theme does not seem to be installed'));
            Tools::redirect('?view=config');
        }

        $this->wallabag->store->updateUserConfig($this->wallabag->user->getId(), 'theme', $newTheme);
        $this->wallabag->messages->add('s', _('you have changed your theme preferences'));

        $currentConfig = $_SESSION['poche_user']->config;
        $currentConfig['theme'] = $newTheme;

        $_SESSION['poche_user']->setConfig($currentConfig);

        Tools::emptyCache();
        Tools::redirect('?view=config');
    }
}
