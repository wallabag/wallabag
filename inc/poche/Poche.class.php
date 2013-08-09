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
    public $user;
    public $store;
    public $tpl;
    public $messages;
    public $pagination;

    function __construct()
    {
        if (file_exists('./install') && !DEBUG_POCHE) {
            Tools::logm('folder /install exists');
            die('To install your poche with sqlite, copy /install/poche.sqlite in /db and delete the folder /install. you have to delete the /install folder before using poche.');
        }

        $this->store = new Database();
        $this->init();
        $this->messages = new Messages();

        # installation
        if(!$this->store->isInstalled())
        {
            $this->install();
        }
    }

    private function init() 
    {
        Tools::initPhp();
        Session::init();

        if (isset($_SESSION['poche_user']) && $_SESSION['poche_user'] != array()) {
            $this->user = $_SESSION['poche_user'];
        }
        else {
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

        # template engine
        $loader = new Twig_Loader_Filesystem(TPL);
        if (DEBUG_POCHE) {
            $twig_params = array();
        }
        else {
            $twig_params = array('cache' => CACHE);
        }
        $this->tpl = new Twig_Environment($loader, $twig_params);
        $this->tpl->addExtension(new Twig_Extensions_Extension_I18n());
        # filter to display domain name of an url
        $filter = new Twig_SimpleFilter('getDomain', 'Tools::getDomain');
        $this->tpl->addFilter($filter);

        # filter for reading time
        $filter = new Twig_SimpleFilter('getReadingTime', 'Tools::getReadingTime');
        $this->tpl->addFilter($filter);

        # Pagination
        $this->pagination = new Paginator($this->user->getConfigValue('pager'), 'p');
    }

    private function install() 
    {
        Tools::logm('poche still not installed');
        echo $this->tpl->render('install.twig', array(
            'token' => Session::getToken()
        ));
        if (isset($_GET['install'])) {
            if (($_POST['password'] == $_POST['password_repeat']) 
                && $_POST['password'] != "" && $_POST['login'] != "") {
                # let's rock, install poche baby !
                $this->store->install($_POST['login'], Tools::encodeString($_POST['password'] . $_POST['login']));
                Session::logout();
                Tools::logm('poche is now installed');
                Tools::redirect();
            }
            else {
                Tools::logm('error during installation');
                Tools::redirect();
            }
        }
        exit();
    }

    public function getDefaultConfig()
    {
        return array(
            'pager' => PAGINATION,
            'language' => LANG,
            );
    }

    /**
     * Call action (mark as fav, archive, delete, etc.)
     */
    public function action($action, Url $url, $id = 0, $import = FALSE)
    {
        switch ($action)
        {
            case 'add':
                if($parametres_url = $url->fetchContent()) {
                    if ($this->store->add($url->getUrl(), $parametres_url['title'], $parametres_url['content'], $this->user->getId())) {
                        Tools::logm('add link ' . $url->getUrl());
                        $sequence = '';
                        if (STORAGE == 'postgres') {
                            $sequence = 'entries_id_seq';
                        }
                        $last_id = $this->store->getLastId($sequence);
                        if (DOWNLOAD_PICTURES) {
                            $content = filtre_picture($parametres_url['content'], $url->getUrl(), $last_id);
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
                }
                else {
                    if (!$import) {
                        $this->messages->add('e', _('error during fetching content : the link wasn\'t added'));
                        Tools::logm('error during content fetch ' . $url->getUrl());
                    }
                }
                if (!$import) {
                    Tools::redirect();
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
                $compare_dev = version_compare(POCHE_VERSION, $dev);
                $compare_prod = version_compare(POCHE_VERSION, $prod);
                $tpl_vars = array(
                    'dev' => $dev,
                    'prod' => $prod,
                    'compare_dev' => $compare_dev,
                    'compare_prod' => $compare_prod,
                );
                Tools::logm('config view');
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
                    $tpl_vars = array(
                        'entry' => $entry,
                        'content' => $content,
                    );
                }
                else {
                    Tools::logm('error in view call : entry is null');
                }
                break;
            default: # home view
                $entries = $this->store->getEntriesByView($view, $this->user->getId());
                $this->pagination->set_total(count($entries));
                $page_links = $this->pagination->page_links('?view=' . $view . '&sort=' . $_SESSION['sort'] . '&');
                $datas = $this->store->getEntriesByView($view, $this->user->getId(), $this->pagination->get_limit());
                $tpl_vars = array(
                    'entries' => $datas,
                    'page_links' => $page_links,
                );
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

    /**
     * checks if login & password are correct and save the user in session.
     * it redirects the user to the $referer link
     * @param  string $referer the url to redirect after login
     * @todo add the return value
     * @return boolean
     */
    public function login($referer)
    {
        if (!empty($_POST['login']) && !empty($_POST['password'])) {
            $user = $this->store->login($_POST['login'], Tools::encodeString($_POST['password'] . $_POST['login']));
            if ($user != array()) {
                # Save login into Session
                Session::login($user['username'], $user['password'], $_POST['login'], Tools::encodeString($_POST['password'] . $_POST['login']), array('poche_user' => new User($user)));

                $this->messages->add('s', _('welcome to your poche'));
                if (!empty($_POST['longlastingsession'])) {
                    $_SESSION['longlastingsession'] = 31536000;
                    $_SESSION['expires_on'] = time() + $_SESSION['longlastingsession'];
                    session_set_cookie_params($_SESSION['longlastingsession']);
                } else {
                    session_set_cookie_params(0);
                }
                session_regenerate_id(true);
                Tools::logm('login successful');
                Tools::redirect($referer);
            }
            $this->messages->add('e', _('login failed: bad login or password'));
            Tools::logm('login failed');
            Tools::redirect();
        } else {
            $this->messages->add('e', _('login failed: you have to fill all fields'));
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
     * @return boolean
     */
    private function importFromInstapaper()
    {
        # TODO gestion des articles favs
        $html = new simple_html_dom();
        $html->load_file('./instapaper-export.html');
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
     * @return boolean 
     */
    private function importFromPocket()
    {
        # TODO gestion des articles favs
        $html = new simple_html_dom();
        $html->load_file('./ril_export.html');
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
     * @return boolean 
     */
    private function importFromReadability()
    {
        # TODO gestion des articles lus / favs
        $str_data = file_get_contents("./readability");
        $data = json_decode($str_data,true);
        Tools::logm('starting import from Readability');

        foreach ($data as $key => $value) {
            $url = '';
            foreach ($value as $attr => $attr_value) {
                if ($attr == 'article__url') {
                    $url = new Url(base64_encode($attr_value));
                }
                $sequence = '';
                if (STORAGE == 'postgres') {
                    $sequence = 'entries_id_seq';
                }
                // if ($attr_value == 'favorite' && $attr_value == 'true') {
                //     $last_id = $this->store->getLastId($sequence);
                //     $this->store->favoriteById($last_id);
                //     $this->action('toogle_fav', $url, $last_id, TRUE);
                // }
                if ($attr_value == 'archive' && $attr_value == 'true') {
                    $last_id = $this->store->getLastId($sequence);
                    $this->action('toggle_archive', $url, $last_id, TRUE);
                }
            }
            if ($url->isCorrect())
                $this->action('add', $url, 0, TRUE);
        }
        $this->messages->add('s', _('import from Readability completed'));
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
        if ($from == 'pocket') {
            return $this->importFromPocket();
        }
        else if ($from == 'readability') {
            return $this->importFromReadability();
        }
        else if ($from == 'instapaper') {
            return $this->importFromInstapaper();
        }
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
}