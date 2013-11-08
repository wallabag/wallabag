<?php
/**
 * poche RSS feeds
 *
 * @package poche
 * @subpackage feeds
 * @license    http://www.gnu.org/licenses/agpl-3.0.html  GNU Affero GPL
 * @author     Nicolas Lœuillet <support@inthepoche.com>
 */

require 'common.php';
require 'vendor/PicoTools/Helper.php';
require 'vendor/PicoFarad/Response.php';
require 'vendor/PicoFarad/Request.php';
require 'vendor/PicoFeed/Writers/Atom.php';

use PicoFarad\Response;
use PicoFarad\Request;
use PicoFeed\Writers\Atom;

// Check token
$user_id = Request\param('id');
$user = Model\get_user_by_id($user_id);
$feed_token = $user['feed_token'];
$request_token = Request\param('token');

// Check status
$authorized_status = array(
    'unread',
    'bookmarks',
    'tag'
    );
$request_status = Request\param('status');

if ($request_status === '' || !in_array($request_status, $authorized_status)) {
    $request_status = 'unread';
}

if ($feed_token !== $request_token) {
    Response\text('Access Forbidden', 403);
}

// Load translations
$language = $user['language'] ?: 'en_US';
if ($language !== 'en_US') PicoTools\Translator\load($language);

// Build Feed
$writer = new Atom;
$writer->title = ucfirst($request_status) . ' - poche';
$writer->site_url = Helper\get_current_base_url();
$writer->feed_url = $writer->site_url.'feed.php?token='.urlencode($feed_token).'&status='.(urlencode($request_status));

if ($request_status === 'bookmarks') {
    $items = Model\get_bookmarks($user_id);
}
elseif ($request_status === 'tag') {
    $tag_id = Request\param('value', 0);
    $items = Model\get_entries_by_tag($tag_id, $user_id);
} else {
    $items = Model\get_items('unread', $user_id);
}

foreach ($items as $item) {

    $article = Model\get_item($item['id'], $user_id);

    $writer->items[] = array(
        'id' => $article['id'],
        'title' => $article['title'],
        'updated' => $article['updated'],
        'url' => $article['url'],
        'content' => $article['content'],
    );
}

Response\xml($writer->execute());