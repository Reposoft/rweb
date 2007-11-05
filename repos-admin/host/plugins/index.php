<?php
/**
 *
 *
 * @package
 */

require( '../../admin.inc.php' );
require( ReposWeb.'conf/Presentation.class.php' );

function isPlugin($folderEntry) {
	return strpos($folderEntry, '.') === false;
}

$pluginsFolder = ReposWeb.'plugins/';
$pluginContents = getDirContents($pluginsFolder);
$plugins = array_filter($pluginContents, 'isPlugin');

$p = new Presentation();
$p->assign('plugins', $plugins);
$p->display();

?>
