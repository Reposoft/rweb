<?php
/**
 * Edit or delete subversion properties.
 * 
 * TODO support folders
 *
 * Due to limitations in how html forms handle spaces,
 * 2+ spaces in a row are posted as 1 space,
 * however this service should be able to handle 2+ spaces.
 */
require('../../conf/Presentation.class.php');
require('../SvnEdit.class.php');

// From svnbook 1.6

// A property name must start with a letter, a colon (:), or an underscore (_); after that, you can also use digits, hyphens (-), and periods (.).
// Subversion allows you to store properties with empty values

// local property modifications can conflict with changes committed by someone else
// a file with a .prej extension contains the details of the conflict
// Until the conflict is resolved, you will see a C in the second column of svn status

// the svn:ignore property is expected to contain a list of newline-delimited file patterns
// (keywords are separated by whitespace)
// Because the svn:externals property has a multiline value, we strongly recommend that you use svn propedit instead of svn propset.

// TODO this code does not remove properties,  it only uses propset

define('VALID_PROPERTY_NAME','/^[a-zA-Z:_][a-zA-Z0-9:_.-]*$/');

if ($_SERVER['REQUEST_METHOD']=='POST') {
	Validation::expect('target','keys','values');
	svnPropset(getTarget(),
		$_REQUEST['keys'], $_REQUEST['values'],
		isset($_REQUEST['message']) ? $_REQUEST['message'] : null);
	return;
}

$target = getTarget();
$template = Presentation::getInstance();
$file = new SvnOpenFile($target);
$template->assign_by_ref('file', $file);
$template->assign('repository', getRepository());
$template->assign('target', $target);
$template->assign('oldname', getPathName($target));
$template->assign('folder', getParent($target));

$cmd = new SvnOpen('proplist');
$cmd->addArgOption('-v');
$cmd->addArgOption('--xml');
$cmd->addArgUrl($file->getUrl());
if ($cmd->exec()) {
	trigger_error(implode("\n",$cmd->getOutput()), E_USER_ERROR);	
}
$output = $cmd->getOutput();

$propval = '';

function propStart($parser, $name, $attrs) {
	global $propval, $template;
	if ($name != 'PROPERTY') return;
	$template->append('keys', $attrs['NAME']);
	$propval = '';
}

function propEnd($parser, $name) {
	global $propval, $template;
	if ($name != 'PROPERTY') return;
	$template->append('values', $propval);
}

function propData($parser, $data) {
	global $propval;
	$propval .= $data;
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

// set the number of fields for new properties
for ($i = 0; $i < 3; $i++) { 
	$template->append('keys', '');
	$template->append('values', '');
}

$template->display();
// done with form page

/**
 * Edits multiple properties on a single target and then commits.
 * The rules are:
 *  Empty key: do nothing.
 *  Empty value: set key to empty (empty property values are ok in snv)
 *  Missing value: propdel the key
 *  Both key and value: propset, if identical to previous value svn will detect as no change.
 */
function svnPropset($target, $keys, $values, $message=null) {
	$presentation = Presentation::getInstance();
	$workingCopy = System::getTempFolder('propedit');
	$targetFolder = getParent(getTargetUrl());
	$filename = getPathName(getTargetUrl());
	// propset can only be done in working copy
	$checkout = new SvnEdit('checkout');
	$checkout->addArgOption('--non-recursive'); // TODO use sparse checkouts
	$checkout->addArgUrl($targetFolder);
	$checkout->addArgPath($workingCopy);
	$checkout->exec('Check out latest version');
	// set the property in local copy
	foreach ($keys as $i => $name) {
		// see rules in method documentation
		if (!$name) continue;
		if (!preg_match(VALID_PROPERTY_NAME, $name)) Validation::error("keys[$i] property name is invalid: $name");
		// TODO validate name according to rule in svn book
		if (!isset($values[$i])) {
			$propdel = new SvnEdit('propdel');
			$propset->addArgOption($name);
			$propset->addArgPath($workingCopy.$filename);
			$propset->exec("Delete property '$name'");
			continue;
		}
		$propset = new SvnEdit('propset');
		// multiline values must be set from file
		// a bug in svn's argument parsing makes "-x-value" cause "illegal option -x"
		if (strContains($values[$i], "\n") || strpos($values[$i], '-') === 0) {
			// normalize newlines TODO check if windows clients use CRLF
			$values[$i] = str_replace("\r\n", "\n", $values[$i]);
			// can not preserve newlinees on command line, must use file
			$proptemp = $workingCopy.$filename.'.proptemp';
			$fp = fopen($workingCopy.$filename.'.proptemp', 'w');
			fwrite($fp, $values[$i]);
			fclose($fp);
			$propset->addArgOption($name);
			$propset->addArgOption('--file', $proptemp);
			// temp file will be cleaned up since it is in the wc
		} else {
			// set value as quoted command line argument   
			$propset->addArgOption($name, $values[$i]);
		}
		$propset->addArgPath($workingCopy.$filename);
		if ($propset->exec("Set property '$name' to '$values[$i]'")) {
			// don't commit, clean up
			System::deleteFolder($workingCopy);
			// error message is already sent to template
			displayEdit($presentation);
			return;
		}
	}
	// commit
	$commit = new SvnEdit('commit');
	$commit->addArgPath($workingCopy);
	if ($message==null) $message = '';
	$commit->addArgOption('-m',$message);
	$commit->exec();
	// clean up
	System::deleteFolder($workingCopy);
	// done
	displayEdit($presentation);
} 

?>
