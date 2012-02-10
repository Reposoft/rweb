<?php
/**
 * Not threasafe, reads proplist to array, given url and optional rev.
 */
define('REPOS_PROPLIST_GROUP_NOT_NAMESPACED', "not namespaced");

function getProplistSorted($targetUrl, $revPeg = null) {
	$p = getProplist($targetUrl, $revPeg);
	uksort($p, 'getProplistSorted_cmpkey');
	return $p;
}

function getProplistSorted_cmpkey($a, $b) {
	return strcasecmp($a, $b);
}

/**
 * For presentation, grouped into namespaces and values as lists (split on newline, svn style)
 * @param {String} $targetUrl to do proplist on
 * @param {Number} $revPeg optional revision
 */
function getProplistGrouped($targetUrl, $revPeg = null) {
	$p = getProplistSorted($targetUrl, $revPeg);
	$g = array();
	foreach ($p as $k => $v) {
		$ns = REPOS_PROPLIST_GROUP_NOT_NAMESPACED;
		$c = strpos($k, ':');
		if ($c > 0) {
			$ns = substr($k, 0, $c);
		}
		if (!array_key_exists($ns, $g)) {
			$g[$ns] = array();
		}
		$g[$ns][$k] = explode("\n", $v); // TODO with \r?
	}
	uksort($g, 'getProplistGrouped_cmpns');
	return $g;
}

function getProplistGrouped_cmpns($a, $b) {
	if ($a == 'svn') return -1;
	if ($b == 'svn') return 1;
	if ($a == REPOS_PROPLIST_GROUP_NOT_NAMESPACED) return 1;
	if ($b == REPOS_PROPLIST_GROUP_NOT_NAMESPACED) return -1;
	return strcasecmp($a, $b);
}

$repos_getProplist_arr = array();
$repos_getProplist_key;
$repos_getProplist_val;

function getProplist($targetUrl, $revPeg = null) {
	global $repos_getProplist_arr, $repos_getProplist_key, $repos_getProplist_val;
	if (count($repos_getProplist_arr) > 0) {
		// could instead reset to empty arr but this is to make sure we don't do extra calls
		trigger_error('Proplist has already been called', E_USER_ERROR);
	}
	$repos_getProplist_val = '';
	
	$cmd = new SvnOpen('proplist');
	$cmd->addArgOption('-v');
	$cmd->addArgOption('--xml');
	if ($revPeg) {
		$cmd->addArgUrlPeg($targetUrl, $revPeg);
	} else {
		$cmd->addArgUrl($targetUrl);
	}
	if ($cmd->exec()) {
		trigger_error(implode("\n",$cmd->getOutput()), E_USER_ERROR);	
	}
	$output = $cmd->getOutput();
	
	$xml_parser = xml_parser_create();
	xml_set_element_handler($xml_parser, "repos_getProplist_propStart", "repos_getProplist_propEnd");
	xml_set_character_data_handler($xml_parser, "repos_getProplist_propData");
	for ($i = 0; $i < count($output);) {
		if (!xml_parse($xml_parser, $output[$i]."\n", count($output) == $i++)) {
			trigger_error(sprintf("XML error: %s at line %d",
				xml_error_string(xml_get_error_code($xml_parser)),
				xml_get_current_line_number($xml_parser)), E_USER_ERROR);
		}
	}
	
	xml_parser_free($xml_parser);
	return $repos_getProplist_arr;
}

function repos_getProplist_propStart($parser, $name, $attrs) {
	global $repos_getProplist_arr, $repos_getProplist_key, $repos_getProplist_val;
	if ($name != 'PROPERTY') return;
	$repos_getProplist_key = $attrs['NAME'];
	$repos_getProplist_val = '';
}

function repos_getProplist_propEnd($parser, $name) {
	global $repos_getProplist_arr, $repos_getProplist_key, $repos_getProplist_val;
	if ($name != 'PROPERTY') return;
	$repos_getProplist_arr[$repos_getProplist_key] = $repos_getProplist_val;
}

function repos_getProplist_propData($parser, $data) {
	global $repos_getProplist_val;
	$repos_getProplist_val .= $data;
}

?>