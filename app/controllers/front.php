<?php
use Poche\Model\Entry;
use Poche\Controller;

use Symfony\Component\HttpFoundation\Request;

$front = $app['controllers_factory'];

// entry
$front->get('/', 'Poche\Controller\EntryController::indexAction');
$front->get('/view/{id}', 'Poche\Controller\EntryController::showAction')
->bind('view_entry');
$front->match('/add', 'Poche\Controller\EntryController::addAction')
->bind('add');
$front->get('/remove/{id}', 'Poche\Controller\EntryController::removeAction')
->bind('remove_entry');
$front->get('/restore/{id}', 'Poche\Controller\EntryController::restoreAction')
->bind('restore_entry');

// bookmarks
$front->get('/bookmarks', 'Poche\Controller\BookmarkController::indexAction');
$front->get('/star/{id}', 'Poche\Controller\BookmarkController::addAction')
->bind('star_entry');
$front->get('/unstar/{id}', 'Poche\Controller\BookmarkController::removeAction')
->bind('unstar_entry');

// archive
$front->get('/archive', 'Poche\Controller\ArchiveController::indexAction');
$front->get('/mark-read/{id}', 'Poche\Controller\ArchiveController::readAction')
->bind('mark_entry_read');
$front->get('/mark-unread/{id}', 'Poche\Controller\ArchiveController::unreadAction')
->bind('mark_entry_unread');

return $front;
