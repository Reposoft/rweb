<?php
/**
 * @package open
 */

require( dirname(dirname(__FILE__))."/SvnOpen.class.php" );

/**
 * Template method
 */
function getListJson($url, $rev=false) {
	$xml = getListXml($url, $rev);
	return getListJsonFromXml($xml);
}

function getListArray($url, $rev=false) {
	$json = getListJson($url, $rev);
	return getListArrayFromJson($json);
}

function getListXml($url, $rev=false) {

$list = new SvnOpen('list');
$list->addArgOption('--xml');
if ($rev) {
	$list->addArgUrlPeg($url, $rev);
} else {
	$list->addArgUrl($url);
}

if ($list->exec()) trigger_error('Could not read entry for URL '.$url, E_USER_ERROR);
// TODO detect access denied

	return implode($list->getOutput(),"\n");
}

// TODO we really need a better xml parser or xml to json
// however xml->json mapping in the general case is non-trivial

function getListJsonFromXml($xml) {

// xml is five levels deep
// maximum one attribute
// attributes never named same as child node

// define the svn variable that wraps it up
$xml = preg_replace('/.*<list\s+path="([^"]+)">/s','{"path":"\1", "list":{',$xml);
// entries must have unique names in json
$xml = preg_replace('/<entry\s+kind="(file|dir)">\s+<name>(.*)<\/name>(.*)<\/entry>/sU','  "\2":{\3"kind":"\1"},',$xml);
// elements with no attributes
$xml = preg_replace('/<(\w+)>(.*)<\/\1>\s*/','"\1":"\2",',$xml);
// elements with one attribute
$xml = preg_replace('/<(\w+)\s+(\w+)="(\d+)">(.*)<\/\1>/sU','"\1":{\4"\2":"\3"},',$xml);
// special treatment of lock, no attribute
$xml = str_replace(array('<lock>',',</lock>'),array('"lock":{','},'),$xml);
// remove last comma and close object
$xml = preg_replace('/,?\s*<\/list>\s+<\/lists>/',"\n}}",$xml);

	return $xml;
}

/**
 * Our current specialized regex parser relies on a sequence of search-replace,
 * which is not trivial to translate to array creation, which is why we go via json.
 * This also helps with validation during development. Performance can be improved later.
 */
function getListArrayFromJson($json) {
	$j = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
	return $j->decode($json);
}

?>
