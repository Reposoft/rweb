<?php
/**
 * Demo pages for the edit plugin.
 *
 * @package
 */
require('../validation/validation.inc.php');
require('edit.inc.php');

$commands = '
<a id="plugins" href="./">plugins:edit</a>
<a id="editempty" href="?test=empty">Empty textarea</a>
<a id="edithtml" href="?test=html">Repos HTML</a>
<a id="edittext" href="?test=text">Plaintext</a>
';

$head = edit_getHeadTags('../../');

$saved = '';

$page = 'index_';
if (isset($_GET['test'])) {
	$page .= $_GET['test'];
} else {
	if (isset($_POST['usertext'])) {
		$saved = $_POST['usertext'];
	}	
}
$page .= '.html';

if (!file_exists($page)) {
	trigger_error("Plugin testpage '$page' not found in this folder", E_USER_ERROR);
	exit;
}

$f = fopen($page, 'r');
$contents = fread($f, 16000);
fclose($f);

$contents = str_replace('</head>', implode("\n",$head).'</head>', $contents);
$contents = str_replace('id="commandbar">', 'id="commandbar">'.$commands, $contents);
if ($saved) {
	$contents = str_replace('id="saved">', 'id="saved">'.htmlspecialchars($saved), $contents);
}

header('Cache-Control: no-cache');
header('Content-Length: '.strlen($contents));
echo($contents);

?>
