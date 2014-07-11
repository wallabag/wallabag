<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://opensource.org/licenses/MIT see COPYING file
 */

define ('POCHE', '1.8.0');
require 'check_setup.php';
require_once 'inc/poche/global.inc.php';


use PicoFarad\Router;
use PicoFarad\Response;
use PicoFarad\Request;
use PicoFarad\Session;

// Called before each action
Router\before(function($action) {

    // Open a session only for the specified directory
    Session\open(dirname($_SERVER['PHP_SELF']));

    // HTTP secure headers
    Response\csp();
    Response\xframe();
    Response\xss();
    Response\nosniff();
});

// Show help
Router\get_action('unread', function() use ($wallabag) {
    $view = 'home';
    $id = 0;

    $tpl_vars = array(
        'referer' => $wallabag->routing->referer,
        'view' => $wallabag->routing->view,
        'poche_url' => Tools::getPocheUrl(),
        'title' => _('wallabag, a read it later open source system'),
        'token' => \Session::getToken(),
        'theme' => $wallabag->tpl->getTheme(),
        'entries' => '',
        'page_links' => '',
        'nb_results' => '',
        'listmode' => (isset($_COOKIE['listmode']) ? true : false),
    );

    $count = $wallabag->store->getEntriesByViewCount($view, $wallabag->user->getId(), $id);

    if ($count > 0) {
        $wallabag->pagination->set_total($count);
        $page_links = str_replace(array('previous', 'next'), array(_('previous'), _('next')),
            $wallabag->pagination->page_links('?view=' . $view . '&sort=' . $_SESSION['sort'] . (($id)?'&id='.$id:'') . '&' ));
        $tpl_vars['entries'] = $wallabag->store->getEntriesByView($view, $wallabag->user->getId(), $wallabag->pagination->get_limit(), $id);
        $tpl_vars['page_links'] = $page_links;
        $tpl_vars['nb_results'] = $count;
    }

    $wallabag->routing->render('home.twig', $tpl_vars);

    Tools::logm('display ' . $view . ' view');

});
