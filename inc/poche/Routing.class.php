<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://opensource.org/licenses/MIT see COPYING file
 */

class Routing
{
    protected $wallabag;
    protected $referer;
    protected $view;
    protected $action;
    protected $id;
    protected $url;
    protected $file;
    protected $defaultVars = array();
    protected $vars = array();

    public function __construct(Poche $wallabag)
    {
        $this->wallabag = $wallabag;
        $this->_init();
    }

    private function _init()
    {
        # Parse GET & REFERER vars
        $this->referer      = empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];
        $this->view         = Tools::checkVar('view', 'home');
        $this->action       = Tools::checkVar('action');
        $this->id           = Tools::checkVar('id');
        $this->autoclose    = Tools::checkVar('autoclose',FALSE);
        $_SESSION['sort']   = Tools::checkVar('sort', 'id');
        $this->url          = new Url((isset ($_GET['url'])) ? $_GET['url'] : '');
    }

    public function run()
    {
        # vars to _always_ send to templates
        $this->defaultVars = array(
            'referer' => $this->referer,
            'view' => $this->view,
            'poche_url' => Tools::getPocheUrl(),
            'title' => _('wallabag, a read it later open source system'),
            'token' => \Session::getToken(),
            'theme' => $this->wallabag->tpl->getTheme()
        );

        $this->_launchAction();
        $this->_defineTplInformation();

        # because messages can be added in $poche->action(), we have to add this entry now (we can add it before)
        $this->vars = array_merge($this->vars, array('messages' => $this->wallabag->messages->display('all', FALSE)));

        $this->_render($this->file, $this->vars);
    }

    private function _defineTplInformation()
    {
        $tplFile = array();
        $tplVars = array();

        if (\Session::isLogged()) {
            $this->wallabag->action($this->action, $this->url, $this->id, FALSE, $this->autoclose);
            $tplFile = Tools::getTplFile($this->view);
            $tplVars = array_merge($this->vars, $this->wallabag->displayView($this->view, $this->id));
        } elseif(ALLOW_REGISTER && isset($_GET['registerform'])) {
            Tools::logm('register');
            $tplFile = Tools::getTplFile('register');
        } elseif (ALLOW_REGISTER && isset($_GET['register'])){
            $this->wallabag->createNewUser($_POST['newusername'], $_POST['password4newuser'], $_POST['newuseremail']);
            Tools::redirect();
        } elseif(isset($_SERVER['PHP_AUTH_USER'])) {
            if($this->wallabag->store->userExists($_SERVER['PHP_AUTH_USER'])) {
                $this->wallabag->login($this->referer);
            } else {
                $this->wallabag->messages->add('e', _('login failed: user doesn\'t exist'));
                Tools::logm('user doesn\'t exist');
                $tplFile = Tools::getTplFile('login');
                $tplVars['http_auth'] = 1;
            }
        } elseif(isset($_SERVER['REMOTE_USER'])) {
            if($this->wallabag->store->userExists($_SERVER['REMOTE_USER'])) {
                $this->wallabag->login($this->referer);
            } else {
                $this->wallabag->messages->add('e', _('login failed: user doesn\'t exist'));
                Tools::logm('user doesn\'t exist');
                $tplFile = Tools::getTplFile('login');
                $tplVars['http_auth'] = 1;
            }
        } else {
            $tplFile = Tools::getTplFile('login');
            $tplVars['http_auth'] = 0;
            \Session::logout();
        }

        $this->file = $tplFile;
        $this->vars = array_merge($this->defaultVars, $tplVars);
    }

    private function _launchAction()
    {
        if (isset($_GET['login'])) {
        	// hello to you
        	$this->wallabag->login($this->referer);
        } elseif (isset($_GET['feed']) && isset($_GET['user_id'])) {
            $tag_id = (isset($_GET['tag_id']) ? intval($_GET['tag_id']) : 0);
            $limit = (isset($_GET['limit']) ? intval($_GET['limit']) : 0);
            $this->wallabag->generateFeeds($_GET['token'], filter_var($_GET['user_id'],FILTER_SANITIZE_NUMBER_INT), $tag_id, $_GET['type'], $limit);
        } //elseif (ALLOW_REGISTER && isset($_GET['register'])) {
            //$this->wallabag->register
        //}
        
        //allowed ONLY to logged in user
        if (\Session::isLogged() === true) 
        {
            if (isset($_GET['logout'])) {
                // see you soon !
                $this->wallabag->logout();
            } elseif (isset($_GET['config'])) {
                // update password
                $this->wallabag->updatePassword($_POST['password'], $_POST['password_repeat']);
            } elseif (isset($_GET['newuser'])) {
                $this->wallabag->createNewUser($_POST['newusername'], $_POST['password4newuser'], $_POST['newuseremail'], true);
            } elseif (isset($_GET['deluser'])) {
                $this->wallabag->deleteUser($_POST['password4deletinguser']);
            } elseif (isset($_GET['epub'])) {
                $epub = new WallabagEpub($this->wallabag, $_GET['method'], $_GET['value']);
                $epub->prepareData();
                $epub->produceEpub();
            } elseif (isset($_GET['mobi'])) {
                $mobi = new WallabagMobi($this->wallabag, $_GET['method'], $_GET['value']);
                $mobi->prepareData();
                $mobi->produceMobi();
            } elseif (isset($_GET['pdf'])) {
                $pdf = new WallabagPDF($this->wallabag, $_GET['method'], $_GET['value']);
                $pdf->prepareData();
                $pdf->producePDF();
            } elseif (isset($_GET['import'])) {
                $import = $this->wallabag->import();
                $tplVars = array_merge($this->vars, $import);
            } elseif (isset($_GET['empty-cache'])) {
                Tools::emptyCache();
            } elseif (isset($_GET['export'])) {
                $this->wallabag->export();
            } elseif (isset($_GET['updatetheme'])) {
                $this->wallabag->tpl->updateTheme($_POST['theme']);
            } elseif (isset($_GET['updatelanguage'])) {
                $this->wallabag->language->updateLanguage($_POST['language']);
            } elseif (isset($_GET['uploadfile'])) {
                $this->wallabag->uploadFile();
            } elseif (isset($_GET['feed']) && isset($_GET['action']) && $_GET['action'] == 'generate') {
                $this->wallabag->updateToken();
            }
            elseif (isset($_GET['plainurl']) && !empty($_GET['plainurl'])) {
                $plainUrl = new Url(base64_encode($_GET['plainurl']));
                $this->wallabag->action('add', $plainUrl);
            }
        }
    }

    public function _render($file, $vars)
    {
        echo $this->wallabag->tpl->render($file, $vars);
    }
} 
