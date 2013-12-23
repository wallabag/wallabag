<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <support@inthepoche.com>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

class Poche
{
    public static $canRenderTemplates = true;
    public static $configFileAvailable = true;

    public $user;
    public $store;
    public $tpl;
    public $messages;
    public $pagination;
    
    private $currentTheme = '';
    private $currentLanguage = '';
    private $notInstalledMessage = array();
    
    # @todo make this dynamic (actually install themes and save them in the database including author information et cetera)
    private $installedThemes = array(
        'default' => array('requires' => array()),
        'dark' => array('requires' => array('default')),
        'dmagenta' => array('requires' => array('default')),
        'solarized' => array('requires' => array('default')),
        'solarized-dark' => array('requires' => array('default'))
    );

    public function __construct()
    {
        if ($this->configFileIsAvailable()) {
            $this->init();
        }
        
        if ($this->themeIsInstalled()) {
            $this->initTpl();
        }
        
        if ($this->systemIsInstalled()) {
            $this->store = new Database();
            $this->messages = new Messages();
            # installation
            if (! $this->store->isInstalled()) {
                $this->install();
            }
            $this->store->checkTags();
        }
    }
    
    private function init() 
    {
        Tools::initPhp();
        Session::$sessionName = 'poche'; 
        Session::init();

        if (isset($_SESSION['poche_user']) && $_SESSION['poche_user'] != array()) {
            $this->user = $_SESSION['poche_user'];
        } else {
            # fake user, just for install & login screens
            $this->user = new User();
            $this->user->setConfig($this->getDefaultConfig());
        }

        # l10n
        $language = $this->user->getConfigValue('language');
        putenv('LC_ALL=' . $language);
        setlocale(LC_ALL, $language);
        bindtextdomain($language, LOCALE); 
        textdomain($language); 

        # Pagination
        $this->pagination = new Paginator($this->user->getConfigValue('pager'), 'p');
        
        # Set up theme
        $themeDirectory = $this->user->getConfigValue('theme');
        
        if ($themeDirectory === false) {
            $themeDirectory = DEFAULT_THEME;
        }
        
        $this->currentTheme = $themeDirectory;

        # Set up language
        $languageDirectory = $this->user->getConfigValue('language');
        
        if ($languageDirectory === false) {
            $languageDirectory = DEFAULT_THEME;
        }
        
        $this->currentLanguage = $languageDirectory;
    }

    public function configFileIsAvailable() {
        if (! self::$configFileAvailable) {
            $this->notInstalledMessage[] = 'You have to rename inc/poche/config.inc.php.new to inc/poche/config.inc.php.';

            return false;
        }

        return true;
    }
    
    public function themeIsInstalled() {
        $passTheme = TRUE;
        # Twig is an absolute requirement for Poche to function. Abort immediately if the Composer installer hasn't been run yet
        if (! self::$canRenderTemplates) {
            $this->notInstalledMessage[] = 'Twig does not seem to be installed. Please initialize the Composer installation to automatically fetch dependencies. Have a look at <a href="http://doc.inthepoche.com/doku.php?id=users:begin:install">the documentation.</a>';
            $passTheme = FALSE;
        }

        if (! is_writable(CACHE)) {
            $this->notInstalledMessage[] = 'You don\'t have write access on cache directory.';

            self::$canRenderTemplates = false;

            $passTheme = FALSE;
        } 
        
        # Check if the selected theme and its requirements are present
        if ($this->getTheme() != '' && ! is_dir(THEME . '/' . $this->getTheme())) {
            $this->notInstalledMessage[] = 'The currently selected theme (' . $this->getTheme() . ') does not seem to be properly installed (Missing directory: ' . THEME . '/' . $this->getTheme() . ')';
            
            self::$canRenderTemplates = false;
            
            $passTheme = FALSE;
        }
        
        foreach ($this->installedThemes[$this->getTheme()]['requires'] as $requiredTheme) {
            if (! is_dir(THEME . '/' . $requiredTheme)) {
                $this->notInstalledMessage[] = 'The required "' . $requiredTheme . '" theme is missing for the current theme (' . $this->getTheme() . ')';
                
                self::$canRenderTemplates = false;
                
                $passTheme = FALSE;
            }
        }

        if (!$passTheme) {
            return FALSE;
        }

        
        return true;
    }
    
    /**
     * all checks before installation.
     * @todo move HTML to template
     * @return boolean 
     */
    public function systemIsInstalled()
    {
        $msg = TRUE;
        
        $configSalt = defined('SALT') ? constant('SALT') : '';
        
        if (empty($configSalt)) {
            $this->notInstalledMessage[] = 'You have not yet filled in the SALT value in the config.inc.php file.';
            $msg = FALSE;
        }
        if (STORAGE == 'sqlite' && ! file_exists(STORAGE_SQLITE)) {
            Tools::logm('sqlite file doesn\'t exist');
            $this->notInstalledMessage[] = 'sqlite file doesn\'t exist, you can find it in install folder. Copy it in /db folder.';
            $msg = FALSE;
        }
        if (is_dir(ROOT . '/install') && ! DEBUG_POCHE) {
            $this->notInstalledMessage[] = 'you have to delete the /install folder before using poche.';
            $msg = FALSE;
        }
        if (STORAGE == 'sqlite' && ! is_writable(STORAGE_SQLITE)) {
            Tools::logm('you don\'t have write access on sqlite file');
            $this->notInstalledMessage[] = 'You don\'t have write access on sqlite file.';
            $msg = FALSE;
        }

        if (! $msg) {
            return false;
        }

        return true;
    }
    
    public function getNotInstalledMessage() {
        return $this->notInstalledMessage;
    }

    private function initTpl()
    {
        $loaderChain = new Twig_Loader_Chain();
       
        # add the current theme as first to the loader chain so Twig will look there first for overridden template files
        try {
            $loaderChain->addLoader(new Twig_Loader_Filesystem(THEME . '/' . $this->getTheme()));
        } catch (Twig_Error_Loader $e) {
            # @todo isInstalled() should catch this, inject Twig later
            die('The currently selected theme (' . $this->getTheme() . ') does not seem to be properly installed (' . THEME . '/' . $this->getTheme() .' is missing)');
        }
        
        # add all required themes to the loader chain
        foreach ($this->installedThemes[$this->getTheme()]['requires'] as $requiredTheme) {
            try {
                $loaderChain->addLoader(new Twig_Loader_Filesystem(THEME . '/' . DEFAULT_THEME));
            } catch (Twig_Error_Loader $e) {
                # @todo isInstalled() should catch this, inject Twig later
                die('The required "' . $requiredTheme . '" theme is missing for the current theme (' . $this->getTheme() . ')');
            }
        }
        
        if (DEBUG_POCHE) {
            $twig_params = array();
        } else {
            $twig_params = array('cache' => CACHE);
        }
        
        $this->tpl = new Twig_Environment($loaderChain, $twig_params);
        $this->tpl->addExtension(new Twig_Extensions_Extension_I18n());
        
        # filter to display domain name of an url
        $filter = new Twig_SimpleFilter('getDomain', 'Tools::getDomain');
        $this->tpl->addFilter($filter);

        # filter for reading time
        $filter = new Twig_SimpleFilter('getReadingTime', 'Tools::getReadingTime');
        $this->tpl->addFilter($filter);
        
        # filter for simple filenames in config view
        $filter = new Twig_SimpleFilter('getPrettyFilename', function($string) { return str_replace(ROOT, '', $string); });
        $this->tpl->addFilter($filter);
    }

    private function install() 
    {
        Tools::logm('poche still not installed');
        echo $this->tpl->render('install.twig', array(
            'token' => Session::getToken(),
            'theme' => $this->getTheme(),
            'poche_url' => Tools::getPocheUrl()
        ));
        if (isset($_GET['install'])) {
            if (($_POST['password'] == $_POST['password_repeat']) 
                && $_POST['password'] != "" && $_POST['login'] != "") {
                # let's rock, install poche baby !
                if ($this->store->install($_POST['login'], Tools::encodeString($_POST['password'] . $_POST['login'])))
                {
                    Session::logout();
                    Tools::logm('poche is now installed');
                    Tools::redirect();
                }
            }
            else {
                Tools::logm('error during installation');
                Tools::redirect();
            }
        }
        exit();
    }
    
    public function getTheme() {
        return $this->currentTheme;
    }

    public function getLanguage() {
        return $this->currentLanguage;
    }
    
    public function getInstalledThemes() {
        $handle = opendir(THEME);
        $themes = array();
        
        while (($theme = readdir($handle)) !== false) {
            # Themes are stored in a directory, so all directory names are themes
            # @todo move theme installation data to database
            if (! is_dir(THEME . '/' . $theme) || in_array($theme, array('..', '.'))) {
                continue;
            }
            
            $current = false;
            
            if ($theme === $this->getTheme()) {
                $current = true;
            }
            
            $themes[] = array('name' => $theme, 'current' => $current);
        }
        
        sort($themes);
        return $themes;
    }

    public function getInstalledLanguages() {
        $handle = opendir(LOCALE);
        $languages = array();
        
        while (($language = readdir($handle)) !== false) {
            # Languages are stored in a directory, so all directory names are languages
            # @todo move language installation data to database
            if (! is_dir(LOCALE . '/' . $language) || in_array($language, array('..', '.'))) {
                continue;
            }
            
            $current = false;
            
            if ($language === $this->getLanguage()) {
                $current = true;
            }
            
            $languages[] = array('name' => $language, 'current' => $current);
        }
        
        return $languages;
    }

    public function getDefaultConfig()
    {   
        return array(
            'pager' => PAGINATION,
            'language' => LANG,
            'theme' => DEFAULT_THEME
        );
    }

    /**
     * Call action (mark as fav, archive, delete, etc.)
     */
    public function action($action, Url $url, $id = 0, $import = FALSE, $autoclose = FALSE)
    {
        switch ($action)
        {
            case 'add':
                $json = file_get_contents(Tools::getPocheUrl() . '/inc/3rdparty/makefulltextfeed.php?url='.urlencode($url->getUrl()).'&max=5&links=preserve&exc=&format=json&submit=Create+Feed');
                $content = json_decode($json, true);
                $title = $content['rss']['channel']['item']['title'];
                $body = $content['rss']['channel']['item']['description'];

                if ($this->store->add($url->getUrl(), $title, $body, $this->user->getId())) {
                    Tools::logm('add link ' . $url->getUrl());
                    $sequence = '';
                    if (STORAGE == 'postgres') {
                        $sequence = 'entries_id_seq';
                    }
                    $last_id = $this->store->getLastId($sequence);
                    if (DOWNLOAD_PICTURES) {
                        $content = filtre_picture($body, $url->getUrl(), $last_id);
                        Tools::logm('updating content article');
                        $this->store->updateContent($last_id, $content, $this->user->getId());
                    }
                    if (!$import) {
                        $this->messages->add('s', _('the link has been added successfully'));
                    }
                }
                else {
                    if (!$import) {
                        $this->messages->add('e', _('error during insertion : the link wasn\'t added'));
                        Tools::logm('error during insertion : the link wasn\'t added ' . $url->getUrl());
                    }
                }

                if (!$import) {
                    if ($autoclose == TRUE) {
                      Tools::redirect('?view=home');
                    } else {
                      Tools::redirect('?view=home&closewin=true');
                    }
                }
                break;
            case 'delete':
                $msg = 'delete link #' . $id;
                if ($this->store->deleteById($id, $this->user->getId())) {
                    if (DOWNLOAD_PICTURES) {
                        remove_directory(ABS_PATH . $id);
                    }
                    $this->messages->add('s', _('the link has been deleted successfully'));
                }
                else {
                    $this->messages->add('e', _('the link wasn\'t deleted'));
                    $msg = 'error : can\'t delete link #' . $id;
                }
                Tools::logm($msg);
                Tools::redirect('?');
                break;
            case 'toggle_fav' :
                $this->store->favoriteById($id, $this->user->getId());
                Tools::logm('mark as favorite link #' . $id);
                if (!$import) {
                    Tools::redirect();
                }
                break;
            case 'toggle_archive' :
                $this->store->archiveById($id, $this->user->getId());
                Tools::logm('archive link #' . $id);
                if (!$import) {
                    Tools::redirect();
                }
                break;
            case 'add_tag' :
                $tags = explode(',', $_POST['value']);
                $entry_id = $_POST['entry_id'];
                foreach($tags as $key => $tag_value) {
                    $value = trim($tag_value);
                    $tag = $this->store->retrieveTagByValue($value);

                    if (is_null($tag)) {
                        # we create the tag
                        $tag = $this->store->createTag($value);
                        $sequence = '';
                        if (STORAGE == 'postgres') {
                            $sequence = 'tags_id_seq';
                        }
                        $tag_id = $this->store->getLastId($sequence);
                    }
                    else {
                        $tag_id = $tag['id'];
                    }

                    # we assign the tag to the article
                    $this->store->setTagToEntry($tag_id, $entry_id);
                }
                Tools::redirect();
                break;
            case 'remove_tag' :
                $tag_id = $_GET['tag_id'];
                $this->store->removeTagForEntry($id, $tag_id);
                Tools::redirect();
                break;
            default:
                break;
        }
    }

    function displayView($view, $id = 0)
    {
        $tpl_vars = array();

        switch ($view)
        {
            case 'config':
                $dev = $this->getPocheVersion('dev');
                $prod = $this->getPocheVersion('prod');
                $compare_dev = version_compare(POCHE, $dev);
                $compare_prod = version_compare(POCHE, $prod);
                $themes = $this->getInstalledThemes();
                $languages = $this->getInstalledLanguages();
                $token = $this->user->getConfigValue('token');
                $http_auth = (isset($_SERVER['PHP_AUTH_USER']) || isset($_SERVER['REMOTE_USER'])) ? true : false;
                $tpl_vars = array(
                    'themes' => $themes,
                    'languages' => $languages,
                    'dev' => $dev,
                    'prod' => $prod,
                    'compare_dev' => $compare_dev,
                    'compare_prod' => $compare_prod,
                    'token' => $token,
                    'user_id' => $this->user->getId(),
                    'http_auth' => $http_auth,
                );
                Tools::logm('config view');
                break;
            case 'edit-tags':
                # tags
                $tags = $this->store->retrieveTagsByEntry($id);
                $tpl_vars = array(
                    'entry_id' => $id,
                    'tags' => $tags,
                );
                break;
            case 'tag':
                $entries = $this->store->retrieveEntriesByTag($id);
                $tag = $this->store->retrieveTag($id);
                $tpl_vars = array(
                    'tag' => $tag,
                    'entries' => $entries,
                );
                break;
            case 'tags':
                $token = $this->user->getConfigValue('token');
                $tags = $this->store->retrieveAllTags();
                $tpl_vars = array(
                    'token' => $token,
                    'user_id' => $this->user->getId(),
                    'tags' => $tags,
                );
                break;
            case 'view':
                $entry = $this->store->retrieveOneById($id, $this->user->getId());
                if ($entry != NULL) {
                    Tools::logm('view link #' . $id);
                    $content = $entry['content'];
                    if (function_exists('tidy_parse_string')) {
                        $tidy = tidy_parse_string($content, array('indent'=>true, 'show-body-only' => true), 'UTF8');
                        $tidy->cleanRepair();
                        $content = $tidy->value;
                    }

                    # flattr checking
                    $flattr = new FlattrItem();
                    $flattr->checkItem($entry['url'], $entry['id']);

                    # tags
                    $tags = $this->store->retrieveTagsByEntry($entry['id']);

                    $tpl_vars = array(
                        'entry' => $entry,
                        'content' => $content,
                        'flattr' => $flattr,
                        'tags' => $tags
                    );
                }
                else {
                    Tools::logm('error in view call : entry is null');
                }
                break;
            default: # home, favorites and archive views 
                $entries = $this->store->getEntriesByView($view, $this->user->getId());
                $tpl_vars = array(
                    'entries' => '',
                    'page_links' => '',
                    'nb_results' => '',
                );
                
                if (count($entries) > 0) {
                    $this->pagination->set_total(count($entries));
                    $page_links = $this->pagination->page_links('?view=' . $view . '&sort=' . $_SESSION['sort'] . '&');
                    $datas = $this->store->getEntriesByView($view, $this->user->getId(), $this->pagination->get_limit());
                    $tpl_vars['entries'] = $datas;
                    $tpl_vars['page_links'] = $page_links;
                    $tpl_vars['nb_results'] = count($entries);
                }
                Tools::logm('display ' . $view . ' view');
                break;
        }

        return $tpl_vars;
    }

    /**
     * update the password of the current user. 
     * if MODE_DEMO is TRUE, the password can't be updated. 
     * @todo add the return value
     * @todo set the new password in function header like this updatePassword($newPassword)
     * @return boolean
     */
    public function updatePassword()
    {
        if (MODE_DEMO) {
            $this->messages->add('i', _('in demo mode, you can\'t update your password'));
            Tools::logm('in demo mode, you can\'t do this');
            Tools::redirect('?view=config');
        }
        else {
            if (isset($_POST['password']) && isset($_POST['password_repeat'])) {
                if ($_POST['password'] == $_POST['password_repeat'] && $_POST['password'] != "") {
                    $this->messages->add('s', _('your password has been updated'));
                    $this->store->updatePassword($this->user->getId(), Tools::encodeString($_POST['password'] . $this->user->getUsername()));
                    Session::logout();
                    Tools::logm('password updated');
                    Tools::redirect();
                }
                else {
                    $this->messages->add('e', _('the two fields have to be filled & the password must be the same in the two fields'));
                    Tools::redirect('?view=config');
                }
            }
        }
    }
    
    public function updateTheme()
    {
        # no data
        if (empty($_POST['theme'])) {
        }
        
        # we are not going to change it to the current theme...
        if ($_POST['theme'] == $this->getTheme()) {
            $this->messages->add('w', _('still using the "' . $this->getTheme() . '" theme!'));
            Tools::redirect('?view=config');
        }
        
        $themes = $this->getInstalledThemes();
        $actualTheme = false;
        
        foreach ($themes as $theme) {
            if ($theme['name'] == $_POST['theme']) {
                $actualTheme = true;
                break;
            }
        }
        
        if (! $actualTheme) {
            $this->messages->add('e', _('that theme does not seem to be installed'));
            Tools::redirect('?view=config');
        }
        
        $this->store->updateUserConfig($this->user->getId(), 'theme', $_POST['theme']);
        $this->messages->add('s', _('you have changed your theme preferences'));
        
        $currentConfig = $_SESSION['poche_user']->config;
        $currentConfig['theme'] = $_POST['theme'];
        
        $_SESSION['poche_user']->setConfig($currentConfig);
        
        Tools::redirect('?view=config');
    }

    public function updateLanguage()
    {
        # no data
        if (empty($_POST['language'])) {
        }
        
        # we are not going to change it to the current language...
        if ($_POST['language'] == $this->getLanguage()) {
            $this->messages->add('w', _('still using the "' . $this->getLanguage() . '" language!'));
            Tools::redirect('?view=config');
        }
        
        $languages = $this->getInstalledLanguages();
        $actualLanguage = false;
        
        foreach ($languages as $language) {
            if ($language['name'] == $_POST['language']) {
                $actualLanguage = true;
                break;
            }
        }
        
        if (! $actualLanguage) {
            $this->messages->add('e', _('that language does not seem to be installed'));
            Tools::redirect('?view=config');
        }
        
        $this->store->updateUserConfig($this->user->getId(), 'language', $_POST['language']);
        $this->messages->add('s', _('you have changed your language preferences'));
        
        $currentConfig = $_SESSION['poche_user']->config;
        $currentConfig['language'] = $_POST['language'];
        
        $_SESSION['poche_user']->setConfig($currentConfig);
        
        Tools::redirect('?view=config');
    }

    /**
     * get credentials from differents sources
     * it redirects the user to the $referer link
     * @return array
     */
    private function credentials() {
        if(isset($_SERVER['PHP_AUTH_USER'])) {
            return array($_SERVER['PHP_AUTH_USER'],'php_auth');
        }
        if(!empty($_POST['login']) && !empty($_POST['password'])) {
            return array($_POST['login'],$_POST['password']);
        }
        if(isset($_SERVER['REMOTE_USER'])) {
            return array($_SERVER['REMOTE_USER'],'http_auth');
        }

        return array(false,false);
     }

    /**
     * checks if login & password are correct and save the user in session.
     * it redirects the user to the $referer link
     * @param  string $referer the url to redirect after login
     * @todo add the return value
     * @return boolean
     */
    public function login($referer)
    {
        list($login,$password)=$this->credentials();
        if($login === false || $password === false) {
            $this->messages->add('e', _('login failed: you have to fill all fields'));
            Tools::logm('login failed');
            Tools::redirect();
        }
        if (!empty($login) && !empty($password)) {
            $user = $this->store->login($login, Tools::encodeString($password . $login));
            if ($user != array()) {
                # Save login into Session
            	$longlastingsession = isset($_POST['longlastingsession']);
                Session::login($user['username'], $user['password'], $login, Tools::encodeString($password . $login), $longlastingsession, array('poche_user' => new User($user)));
                $this->messages->add('s', _('welcome to your poche'));
                Tools::logm('login successful');
                Tools::redirect($referer);
            }
            $this->messages->add('e', _('login failed: bad login or password'));
            Tools::logm('login failed');
            Tools::redirect();
        }
    }

    /**
     * log out the poche user. It cleans the session.
     * @todo add the return value
     * @return boolean 
     */
    public function logout()
    {
        $this->user = array();
        Session::logout();
        $this->messages->add('s', _('see you soon!'));
        Tools::logm('logout');
        Tools::redirect();
    }

    /**
     * import from Instapaper. poche needs a ./instapaper-export.html file
     * @todo add the return value
     * @param string $targetFile the file used for importing
     * @return boolean
     */
    private function importFromInstapaper($targetFile)
    {
        # TODO gestion des articles favs
        $html = new simple_html_dom();
        $html->load_file($targetFile);
        Tools::logm('starting import from instapaper');

        $read = 0;
        $errors = array();
        foreach($html->find('ol') as $ul)
        {
            foreach($ul->find('li') as $li)
            {
                $a = $li->find('a');
                $url = new Url(base64_encode($a[0]->href));
                $this->action('add', $url, 0, TRUE);
                if ($read == '1') {
                    $sequence = '';
                    if (STORAGE == 'postgres') {
                        $sequence = 'entries_id_seq';
                    }
                    $last_id = $this->store->getLastId($sequence);
                    $this->action('toggle_archive', $url, $last_id, TRUE);
                }
            }

            # the second <ol> is for read links
            $read = 1;
        }
        $this->messages->add('s', _('import from instapaper completed'));
        Tools::logm('import from instapaper completed');
        Tools::redirect();
    }

    /**
     * import from Pocket. poche needs a ./ril_export.html file
     * @todo add the return value
     * @param string $targetFile the file used for importing
     * @return boolean 
     */
    private function importFromPocket($targetFile)
    {
        # TODO gestion des articles favs
        $html = new simple_html_dom();
        $html->load_file($targetFile);
        Tools::logm('starting import from pocket');

        $read = 0;
        $errors = array();
        foreach($html->find('ul') as $ul)
        {
            foreach($ul->find('li') as $li)
            {
                $a = $li->find('a');
                $url = new Url(base64_encode($a[0]->href));
                $this->action('add', $url, 0, TRUE);
                if ($read == '1') {
                        $sequence = '';
                        if (STORAGE == 'postgres') {
                            $sequence = 'entries_id_seq';
                        }
                    $last_id = $this->store->getLastId($sequence);
                    $this->action('toggle_archive', $url, $last_id, TRUE);
                }
            }
            
            # the second <ul> is for read links
            $read = 1;
        }
        $this->messages->add('s', _('import from pocket completed'));
        Tools::logm('import from pocket completed');
        Tools::redirect();
    }

    /**
     * import from Readability. poche needs a ./readability file
     * @todo add the return value
     * @param string $targetFile the file used for importing
     * @return boolean 
     */
    private function importFromReadability($targetFile)
    {
        # TODO gestion des articles lus / favs
        $str_data = file_get_contents($targetFile);
        $data = json_decode($str_data,true);
        Tools::logm('starting import from Readability');
        $count = 0;
        foreach ($data as $key => $value) {
            $url = NULL;
            $favorite = FALSE;
            $archive = FALSE;
            foreach ($value as $attr => $attr_value) {
                if ($attr == 'article__url') {
                    $url = new Url(base64_encode($attr_value));
                }
                $sequence = '';
                if (STORAGE == 'postgres') {
                    $sequence = 'entries_id_seq';
                }
                if ($attr_value == 'true') {
                    if ($attr == 'favorite') {
                        $favorite = TRUE;
                    }
                    if ($attr == 'archive') {
                        $archive = TRUE;
                    }
                }
            }
            # we can add the url
            if (!is_null($url) && $url->isCorrect()) {
                $this->action('add', $url, 0, TRUE);
                $count++;
                if ($favorite) {
                    $last_id = $this->store->getLastId($sequence);
                    $this->action('toggle_fav', $url, $last_id, TRUE);
                }
                if ($archive) {
                    $last_id = $this->store->getLastId($sequence);
                    $this->action('toggle_archive', $url, $last_id, TRUE);
                }
            }
        }
        $this->messages->add('s', _('import from Readability completed. ' . $count . ' new links.'));
        Tools::logm('import from Readability completed');
        Tools::redirect();
    }

    /**
     * import datas into your poche
     * @param  string $from name of the service to import : pocket, instapaper or readability
     * @todo add the return value
     * @return boolean       
     */
    public function import($from)
    {
        $providers = array(
            'pocket' => 'importFromPocket',
            'readability' => 'importFromReadability',
            'instapaper' => 'importFromInstapaper'
        );
        
        if (! isset($providers[$from])) {
            $this->messages->add('e', _('Unknown import provider.'));
            Tools::redirect();
        }
        
        $targetDefinition = 'IMPORT_' . strtoupper($from) . '_FILE';
        $targetFile = constant($targetDefinition);
        
        if (! defined($targetDefinition)) {
            $this->messages->add('e', _('Incomplete inc/poche/define.inc.php file, please define "' . $targetDefinition . '".'));
            Tools::redirect();
        }
        
        if (! file_exists($targetFile)) {
            $this->messages->add('e', _('Could not find required "' . $targetFile . '" import file.'));
            Tools::redirect();
        }
        
        $this->$providers[$from]($targetFile);
    }

    /**
     * export poche entries in json
     * @return json all poche entries
     */
    public function export()
    {
        $entries = $this->store->retrieveAll($this->user->getId());
        echo $this->tpl->render('export.twig', array(
            'export' => Tools::renderJson($entries),
        ));
        Tools::logm('export view');
    }

    /**
     * Checks online the latest version of poche and cache it
     * @param  string $which 'prod' or 'dev'
     * @return string        latest $which version
     */
    private function getPocheVersion($which = 'prod')
    {
        $cache_file = CACHE . '/' . $which;

        # checks if the cached version file exists
        if (file_exists($cache_file) && (filemtime($cache_file) > (time() - 86400 ))) {
           $version = file_get_contents($cache_file);
        } else {
           $version = file_get_contents('http://static.inthepoche.com/versions/' . $which);
           file_put_contents($cache_file, $version, LOCK_EX);
        }
        return $version;
    }

    public function generateToken()
    {
        if (ini_get('open_basedir') === '') {
            $token = substr(base64_encode(file_get_contents('/dev/urandom', false, null, 0, 20)), 0, 15);
        }
        else {
            $token = substr(base64_encode(uniqid(mt_rand(), true)), 0, 20);
        }

        $this->store->updateUserConfig($this->user->getId(), 'token', $token);
        $currentConfig = $_SESSION['poche_user']->config;
        $currentConfig['token'] = $token;
        $_SESSION['poche_user']->setConfig($currentConfig);
    }

    public function generateFeeds($token, $user_id, $tag_id, $type = 'home')
    {
        $allowed_types = array('home', 'fav', 'archive', 'tag');
        $config = $this->store->getConfigUser($user_id);

        if (!in_array($type, $allowed_types) ||
            $token != $config['token']) {
            die(_('Uh, there is a problem while generating feeds.'));
        }
        // Check the token

        $feed = new FeedWriter(RSS2);
        $feed->setTitle('poche - ' . $type . ' feed');
        $feed->setLink(Tools::getPocheUrl());
        $feed->setChannelElement('updated', date(DATE_RSS , time()));
        $feed->setChannelElement('author', 'poche');

        if ($type == 'tag') {
            $entries = $this->store->retrieveEntriesByTag($tag_id);
        }
        else {
            $entries = $this->store->getEntriesByView($type, $user_id);
        }

        if (count($entries) > 0) {
            foreach ($entries as $entry) {
                $newItem = $feed->createNewItem();
                $newItem->setTitle(htmlentities($entry['title']));
                $newItem->setLink(Tools::getPocheUrl() . '?view=view&amp;id=' . $entry['id']);
                $newItem->setDate(time());
                $newItem->setDescription($entry['content']);
                $feed->addItem($newItem);
            }
        }

        $feed->genarateFeed();
        exit;
    }
}
