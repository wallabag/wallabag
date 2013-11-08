<?php
/**
 * phpDocumentor
 *
 * PHP Version 5
 *
 * @category  phpDocumentor
 * @package   Search
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

// get search term
$term = strtolower($_GET['term']);

// find term in XML document
$xml = new DOMDocument();
$xml->load('search_index.xml');
$xpath = new DOMXPath($xml);

$qry = $xpath->query(
    "//value[contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', "
    . "'abcdefghijklmnopqrstuvwxyz'), '$term')]/.."
);
$results = array();

/** @var DOMElement $element */
foreach ($qry as $element) {
    /** @var DomNodeList $value  */
    $value     = $element->getElementsByTagName('value');
    $id        = $element->getElementsByTagName('id');
    $type      = $element->getElementsByTagName('type');
    $results[] = '{ "value": "' . addslashes($value->item(0)->nodeValue)
    . '", "id": "' . addslashes($id->item(0)->nodeValue)
    . '", "type": "' . addslashes($type->item(0)->nodeValue) . '" }';
}

echo '[' . implode(', ', $results) . ']';
