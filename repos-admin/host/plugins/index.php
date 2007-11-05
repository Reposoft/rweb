<?php
/**
 *
 *
 * @package
 */

require('../admin.inc.php');
require('../../conf/Presentation.class.php');

function isPlugin($folderEntry) {
	return strpos($folderEntry, '.') === false;
}

$pluginsFolder = dirname(dirname(dirname(__FILE__))).'/plugins/';
$pluginContents = getDirContents($pluginsFolder);
$plugins = array_filter($pluginContents, 'isPlugin');

$p = new Presentation();
$p->assign('plugins', $plugins);
$p->display();

?>
