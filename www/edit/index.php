<?php
define('DIR',dirname(__FILE__).DIRECTORY_SEPARATOR);
define('PARENT_DIR', dirname(rtrim(DIR, DIRECTORY_SEPARATOR)));
if(!isset($_GET['action'])) {
	echo("Error: no operation selected");
	exit;
}
header('Location: '.$_GET['action'].'.php?'.$_SERVER['QUERY_STRING']);
?>