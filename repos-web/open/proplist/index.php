<?php
// get the properties of a file or folder as JSON 

require("../SvnOpen.class.php" );
require_once("../../lib/json/json.php" );

$url = getTargetUrl();
$revisionRule = new RevisionRule();
Validation::expect('target');

$cmd = new SvnOpen('proplist');
$cmd->addArgOption('-v');
$cmd->addArgOption('--xml');
if ($revisionRule->getValue()) {
	$cmd->addArgUrlPeg($url, $revisionRule->getValue());
} else {
	$cmd->addArgUrl($url);
}

// This is for evaluation of Accept header usage. Should be aligned with WEBSERVICE_KEY concept in repos.properties.php
if (strBegins($_SERVER['HTTP_ACCEPT'], 'text/xml')) {
	header('Content-type: text/xml');
	$cmd->passthru();
	exit;
}

// Execute to buffer and return default format
if ($cmd->exec()) {
	trigger_error(implode("\n",$cmd->getOutput()), E_USER_ERROR);	
}

$output = $cmd->getOutput(); // Currently not possible to stream command output directly to SAX parser
header('Content-type: text/plain');
echo '{';
$isProperty = false;
$count = 0;

function propStart($parser, $name, $attrs) {
	global $isProperty, $count;
	if ($name == 'TARGET') echo '"target": "'.$attrs['PATH'].'"';
	if ($name == 'PROPERTY') {
		echo ",\n";
		if (!$count++) echo '"proplist":'."{\n";
		echo '"'.$attrs['NAME'].'":"';
		$isProperty = true;
	}
}

function propEnd($parser, $name) {
	global $isProperty, $count;
	if ($name == 'PROPERTY') echo '"';
	if ($count && $name == 'PROPERTIES') echo '}';
	$isProperty = false;
}

function propData($parser, $data) {
	global $isProperty;
	if ($isProperty) echo str_replace('"', '\"', str_replace("\n", '\n', $data));
}

$xml_parser = xml_parser_create();
xml_set_element_handler($xml_parser, "propStart", "propEnd");
xml_set_character_data_handler($xml_parser, "propData");
for ($i = 0; $i < count($output);) {
	if (!xml_parse($xml_parser, $output[$i]."\n", count($output) == $i++)) {
		trigger_error(sprintf("XML error: %s at line %d",
			xml_error_string(xml_get_error_code($xml_parser)),
			xml_get_current_line_number($xml_parser)), E_USER_ERROR);
	}
}

xml_parser_free($xml_parser);
echo "}\n";

// to get all the properties of a specific type for a tree,
// use propget -R [propertyname] path
// but that's probably a different service

?>
