<?php
define('PARENT_DIR',substr(dirname(__FILE__), 0, strrpos(rtrim(strtr(dirname(__FILE__),'\\','/'),'/'),'/')));
require( PARENT_DIR."/language.inc.php" );
if(!isset($_GET['show'])) {
	header('Location: '.getLocaleFile());
	exit;
}
header('Location: '.$_GET['show'].'.php');
?>