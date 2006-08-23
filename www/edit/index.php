<?php
require_once(dirname(dirname(__FILE__)).'/conf/repos.properties.php');
// forward to an action
if(!isset($_GET['action'])) {
	echo("Error: no operation selected");
	exit;
}

header('Location: '.$_GET['action'].'.php?'.$_SERVER['QUERY_STRING']);
?>