<?php
/**
 * Executes as post-commit hook.
 * The output will not be read by anyone.
 *
 * Testurl: http://test.repos.se/repos/admin/post-commit/?repopath=/srv/www/vhosts/repos.se/test/repo&rev=1
 * 
 * @package admin
 * @deprecated use sdmin/hooks/ instead
 */
require('../../conf/Command.class.php');

$home = getConfig('home_path');
if (!$home) {
	trigger_error('home_path not set in configuration, can not execute hook scripts');
}

$path = $_GET['repopath'];
// TODO validate that repo is the configured
$rev = $_GET['rev'];
// TODO validate that rev is integer

$c = new Command('svnlook');
$c->addArgOption('changed');
$c->addArgOption('-r '.$rev);
$c->addArg($path);
$c->exec();

$export = array(
	'administration/trunk/repos-access' => getConfig('admin_folder').getConfig('access_file')
);

$pattern = '/^([ADU_])([U\s])\s+(.*)/';
foreach ($c->getOutput() as $change) {
	preg_match($pattern, $change, $matches);
	$entry = trim($matches[3]);
	if (isset($export[$entry]) && ($matches[1] == 'U' || $matches[1] == 'A')) {
		_exportFile($path, '/'.$entry, $rev, $export[$entry]);	
	} else {
		// do nothing
	}
}

function _exportFile($repo, $path, $revision, $destinationFromHome) {
	$c = new Command('svnlook');
	$c->addArgOption('cat');
	$c->addArgOption('-r '.$revision);
	$c->addArg($repo);
	$c->addArg($path);
	$c->exec();
	$out = $c->getOutput();
	if ($c->getExitcode()) {
		trigger_error("Could not read committed file $pathFromRoot revision $revision: ".implode("\n",$out), E_USER_ERROR);
	} else {
		$handle = fopen($destinationFromHome, 'w');
		for ($i = 0; $i < count($out); $i++) {
			fwrite($handle, $out[$i].System::getNewline());
		}
		fclose($handle);
	}
}

?>
