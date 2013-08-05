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
    public $store;
    public $tpl;
    public $messages;
    public $pagination;

    function __construct($storage_type)
    {
        $this->store = new $storage_type();
        $this->init();
        $this->messages = new Messages();

        # installation
        if(!$this->store->isInstalled())
        {
            $this->install();
        }

        $this->saveUser();
    }

    private function init() 
    {
        # l10n
        putenv('LC_ALL=' . LANG);
        setlocale(LC_ALL, LANG);
        bindtextdomain(LANG, LOCALE); 
        textdomain(LANG); 

        # template engine
        $loader = new Twig_Loader_Filesystem(TPL);
        $this->tpl = new Twig_Environment($loader, array(
            'cache' => CACHE,
        ));
        $this->tpl->addExtension(new Twig_Extensions_Extension_I18n());
        # filter to display domain name of an url
        $filter = new Twig_SimpleFilter('getDomain', 'Tools::getDomain');
        $this->tpl->addFilter($filter);

        $this->pagination = new Paginator(PAGINATION, 'p');

        Tools::initPhp();
        Session::init();
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

    private function saveUser()
    {
        $_SESSION['login'] = (isset ($_SESSION['login'])) ? $_SESSION['login'] : $this->store->getLogin();
        $_SESSION['pass'] = (isset ($_SESSION['pass'])) ? $_SESSION['pass'] : $this->store->getPassword();
    }

    /**
     * Call action (mark as fav, archive, delete, etc.)
     */
    public function action($action, Url $url, $id = 0)
    {
        switch ($action)
        {
            case 'add':
                if($parametres_url = $url->fetchContent()) {
                    if ($this->store->add($url->getUrl(), $parametres_url['title'], $parametres_url['content'])) {
                        Tools::logm('add link ' . $url->getUrl());
                        $last_id = $this->store->getLastId();
                        if (DOWNLOAD_PICTURES) {
                            $content = filtre_picture($parametres_url['content'], $url->getUrl(), $last_id);
                        }
                        $this->messages->add('s', _('the link has been added successfully'));
                    }
                    else {
                        $this->messages->add('e', _('error during insertion : the link wasn\'t added'));
                        Tools::logm('error during insertion : the link wasn\'t added');
                    }
                }
                else {
                    $this->messages->add('e', _('error during fetching content : the link wasn\'t added'));
                    Tools::logm('error during content fetch');
                }
                Tools::redirect();
                break;
            case 'delete':
                if ($this->store->deleteById($id)) {
                    if (DOWNLOAD_PICTURES) {
                        remove_directory(ABS_PATH . $id);
                    }
                    $this->messages->add('s', _('the link has been deleted successfully'));
                    Tools::logm('delete link #' . $id);
                }
                else {
                    $this->messages->add('e', _('the link wasn\'t deleted'));
                    Tools::logm('error : can\'t delete link #' . $id);
                }
                Tools::redirect();
                break;
            case 'toggle_fav' :
                $this->store->favoriteById($id);
                Tools::logm('mark as favorite link #' . $id);
                Tools::redirect();
                break;
            case 'toggle_archive' :
                $this->store->archiveById($id);
                Tools::logm('archive link #' . $id);
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
                $entry = $this->store->retrieveOneById($id);
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
                    Tools::logm('error in view call : entry is NULL');
                }
                break;
            default: # home view
                $entries = $this->store->getEntriesByView($view);
                $this->pagination->set_total(count($entries));
                $page_links = $this->pagination->page_links('?view=' . $view . '&sort=' . $_SESSION['sort'] . '&');
                $datas = $this->store->getEntriesByView($view, $this->pagination->get_limit());
                $tpl_vars = array(
                    'entries' => $datas,
                    'page_links' => $page_links,
                );
                Tools::logm('display ' . $view . ' view');
                break;
        }

        return $tpl_vars;
    }

    public function updatePassword()
    {
        if (MODE_DEMO) {
            $this->messages->add('i', 'in demo mode, you can\'t update your password');
            Tools::logm('in demo mode, you can\'t do this');
            Tools::redirect('?view=config');
        }
        else {
            if (isset($_POST['password']) && isset($_POST['password_repeat'])) {
                if ($_POST['password'] == $_POST['password_repeat'] && $_POST['password'] != "") {
                    Tools::logm('password updated');
                    $this->messages->add('s', 'your password has been updated');
                    $this->store->updatePassword(Tools::encodeString($_POST['password'] . $_SESSION['login']));
                    Session::logout();
                    Tools::redirect();
                }
                else {
                    $this->messages->add('e', 'the two fields have to be filled & the password must be the same in the two fields');
                    Tools::redirect('?view=config');
                }
            }
        }
    }

    public function login($referer)
    {
        if (!empty($_POST['login']) && !empty($_POST['password'])) {
            if (Session::login($_SESSION['login'], $_SESSION['pass'], $_POST['login'], Tools::encodeString($_POST['password'] . $_POST['login']))) {
                Tools::logm('login successful');
                $this->messages->add('s', 'welcome to your poche');
                if (!empty($_POST['longlastingsession'])) {
                    $_SESSION['longlastingsession'] = 31536000;
                    $_SESSION['expires_on'] = time() + $_SESSION['longlastingsession'];
                    session_set_cookie_params($_SESSION['longlastingsession']);
                } else {
                    session_set_cookie_params(0);
                }
                session_regenerate_id(true);
                Tools::redirect($referer);
            }
            $this->messages->add('e', 'login failed: bad login or password');
            Tools::logm('login failed');
            Tools::redirect();
        } else {
            $this->messages->add('e', 'login failed: you have to fill all fields');
            Tools::logm('login failed');
            Tools::redirect();
        }
    }

    public function logout()
    {
        $this->messages->add('s', 'see you soon!');
        Tools::logm('logout');
        Session::logout();
        Tools::redirect();
    }

    private function importFromInstapaper()
    {
        # TODO gestion des articles favs
        $html = new simple_html_dom();
        $html->load_file('./instapaper-export.html');

        $read = 0;
        $errors = array();
        foreach($html->find('ol') as $ul)
        {
            foreach($ul->find('li') as $li)
            {
                $a = $li->find('a');
                $url = new Url(base64_encode($a[0]->href));
                $this->action('add', $url);
                if ($read == '1') {
                    $last_id = $this->store->getLastId();
                    $this->store->archiveById($last_id);
                }
            }

            # the second <ol> is for read links
            $read = 1;
        }
        $this->messages->add('s', 'import from instapaper completed');
        Tools::logm('import from instapaper completed');
        Tools::redirect();
    }

    private function importFromPocket()
    {
        # TODO gestion des articles favs
        $html = new simple_html_dom();
        $html->load_file('./ril_export.html');

        $read = 0;
        $errors = array();
        foreach($html->find('ul') as $ul)
        {
            foreach($ul->find('li') as $li)
            {
                $a = $li->find('a');
                $url = new Url(base64_encode($a[0]->href));
                $this->action('add', $url);
                if ($read == '1') {
                    $last_id = $this->store->getLastId();
                    $this->store->archiveById($last_id);
                }
            }
            
            # the second <ul> is for read links
            $read = 1;
        }
        $this->messages->add('s', 'import from pocket completed');
        Tools::logm('import from pocket completed');
        Tools::redirect();
    }

    private function importFromReadability()
    {
        # TODO gestion des articles lus / favs
        $str_data = file_get_contents("./readability");
        $data = json_decode($str_data,true);

        foreach ($data as $key => $value) {
            $url = '';
            foreach ($value as $attr => $attr_value) {
                if ($attr == 'article__url') {
                    $url = new Url(base64_encode($attr_value));
                }
                // if ($attr_value == 'favorite' && $attr_value == 'true') {
                //     $last_id = $this->store->getLastId();
                //     $this->store->favoriteById($last_id);
                // }
                // if ($attr_value == 'archive' && $attr_value == 'true') {
                //     $last_id = $this->store->getLastId();
                //     $this->store->archiveById($last_id);
                // }
            }
            if ($url->isCorrect())
                $this->action('add', $url);
        }
        $this->messages->add('s', 'import from Readability completed');
        Tools::logm('import from Readability completed');
        Tools::redirect();
    }

    public function import($from)
    {
        if ($from == 'pocket') {
            $this->importFromPocket();
        }
        else if ($from == 'readability') {
            $this->importFromReadability();
        }
        else if ($from == 'instapaper') {
            $this->importFromInstapaper();
        }
    }

    public function export()
    {
        $entries = $this->store->retrieveAll();
        echo $this->tpl->render('export.twig', array(
            'export' => Tools::renderJson($entries),
        ));
        Tools::logm('export view');
    }

    private function getPocheVersion($which = 'prod')
    {
        $cache_file = CACHE . '/' . $which;
        if (file_exists($cache_file) && (filemtime($cache_file) > (time() - 86400 ))) {
           $version = file_get_contents($cache_file);
        } else {
           $version = file_get_contents('http://www.inthepoche.com/' . $which);
           file_put_contents($cache_file, $version, LOCK_EX);
        }
        return $version;
    }
}