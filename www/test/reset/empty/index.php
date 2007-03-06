<?php
/**
 *
 *
 * @package
 */
 
require(dirname(dirname(__FILE__)).'/setup.inc.php');

setup_deleteCurrent();

$report->ok('Created empty repository folder '.$repo);
if ($b = getConfig('backup_folder')) {
	$rm = System::isWindows() ? 'del '.strtr("$b*", '/', '\\') : 'rm '."$b*";	
	$report->info('To delete backup files, do "'.$rm.'".');
}

$rm .= ' '.getConfig('backup_folder');

$report->info('You may wish to proceed to '.'<form action="../../../admin/create/">'.
'<input type="submit" id="create" value="create repository"/></form>');

$report->display();

?>
