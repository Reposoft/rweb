<?php
require_once(dirname(dirname(__FILE__)).'/conf/repos.properties.php');
// show a result using redirect-after-post from edit.class.php
if(isset($_GET['result'])) {
	$resultFile = getTempDir('results') . $_GET['result'];
	$handle = fopen($resultFile, "r");
	fpassthru($handle);
	fclose($handle);
	exit;
}
// forward to an action
if(!isset($_GET['action'])) {
	echo("Error: no operation selected");
	exit;
}

header('Location: '.$_GET['action'].'.php?'.$_SERVER['QUERY_STRING']);
?>