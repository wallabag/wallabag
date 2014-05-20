<?php
use Wallabag\Model\Entry;
use Wallabag\Controller;

use Symfony\Component\HttpFoundation\Request;

$front = $app['controllers_factory'];

// entry
$front->get('/', 'Wallabag\Controller\EntryController::indexAction');
$front->get('/view/{id}', 'Wallabag\Controller\EntryController::showAction')
->bind('view_entry');
$front->match('/add', 'Wallabag\Controller\EntryController::addAction')
->bind('add');
$front->get('/remove/{id}', 'Wallabag\Controller\EntryController::removeAction')
->bind('remove_entry');
$front->get('/restore/{id}', 'Wallabag\Controller\EntryController::restoreAction')
->bind('restore_entry');

// bookmarks
$front->get('/bookmarks', 'Wallabag\Controller\BookmarkController::indexAction');
$front->match('/star/{id}', 'Wallabag\Controller\BookmarkController::addAction')
->bind('star_entry');
$front->match('/unstar/{id}', 'Wallabag\Controller\BookmarkController::removeAction')
->bind('unstar_entry');

// archive
$front->get('/archive', 'Wallabag\Controller\ArchiveController::indexAction');
$front->match('/mark-read/{id}', 'Wallabag\Controller\ArchiveController::readAction')
->bind('mark_entry_read');
$front->match('/mark-unread/{id}', 'Wallabag\Controller\ArchiveController::unreadAction')
->bind('mark_entry_unread');

return $front;
