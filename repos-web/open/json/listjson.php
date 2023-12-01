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

/**
 * @return {array(String)} list output
 */
function getListXml($url, $rev=false) {

$list = new SvnOpen('list');
$list->addArgOption('--xml');
if ($rev) {
	$list->addArgUrlPeg($url, $rev);
} else {
	$list->addArgUrl($url);
}

	if ($list->exec()) {
		$err = implode("\n", array_slice($list->getOutput(), -1));
		if (strBegins($err,'svn: E200009')) {
			header('HTTP/1.1 404 Not Found', true, 404);
		} else {
			header('HTTP/1.1 500 Internal Server Error', true, 500);
		}
		echo '{"end": "'.preg_replace('/"/', '\\"', $err);
		echo '"}'."\n";
		exit;
	}
// TODO detect access denied

	return $list->getOutput();
}

// globals for parsing
$parsed = NULL;
$current = NULL;
$currentcommit = NULL;
$currentlock = NULL;
$currentkey = NULL;

function xstart($parser, $name, $attrs) {
	global $parsed, $current, $currentcommit, $currentlock, $currentkey;
	if ($name == 'LISTS') {
		// ignored because we don't support multiple list elements
	} else if ($name == 'LIST') {
		$parsed = array();
		$parsed['path'] = $attrs['PATH'];
		$parsed['list'] = array();
	} else if ($name == 'ENTRY') {
		$current = array();
		$current['kind'] = $attrs['KIND'];
	} else if ($name == 'LOCK') {
		$currentlock = array();
	} else if ($name == 'COMMIT') {
		$currentcommit = array();
		$currentcommit['revision'] = $attrs['REVISION'];
	} else {
		$currentkey = strtolower($name);
	}
}

function xend($parser, $name) {
	global $parsed, $current, $currentcommit, $currentlock, $currentkey;
	if ($name == 'LIST') {
		if ($currentkey) trigger_error('Unexpected state: element');
		if (!is_null($currentlock)) trigger_error('Unexpected state: lock');
		if (!is_null($currentcommit)) trigger_error('Unexpected state: commit');
	} else if ($name == 'LOCK') {
		$current['lock'] = $currentlock;
		$currentlock = NULL;
	} else if ($name == 'COMMIT') {
		$current['commit'] = $currentcommit;
		$currentcommit = NULL;
	} else if ($name == 'ENTRY') {
		$parsed['list'][$current['name']] = $current;
		$current = NULL;
	} else {
		$currentkey = NULL;
	}
}

function xdata($parser, $data) {
	global $parsed, $current, $currentcommit, $currentlock, $currentkey;
	if (!$currentkey) return;
	if (is_array($currentlock)) {
		$currentlock[$currentkey] = $data;
	} else if (is_array($currentcommit)) {
		$currentcommit[$currentkey] = $data;
	} else {
		$current[$currentkey] = $data;
	}
}

/**
 * @param {array(String)} svn list xml
 */
function getListJsonFromXml($xml) {
	global $parsed;

	$xml_parser = xml_parser_create();
	xml_set_element_handler($xml_parser, "xstart", "xend");
	xml_set_character_data_handler($xml_parser, "xdata");

	for ($i = 0; $i < count($xml);) {
		if (!xml_parse($xml_parser, $xml[$i]."\n", count($xml) == $i++)) {
			trigger_error(sprintf("XML error: %s at line %d",
				xml_error_string(xml_get_error_code($xml_parser)),
				xml_get_current_line_number($xml_parser)), E_USER_ERROR);
		}
	}

	xml_parser_free($xml_parser);

	return json_encode($parsed, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
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
