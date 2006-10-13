<?php
require_once(dirname(dirname(__FILE__)).'/conf/repos.properties.php');
// forward to an action
if(!isset($_GET['action'])) {
	trigger_error("No action selected", E_USER_ERROR);
}

header('Location: '.getWebapp().'edit/'.$_GET['action'].'/?'.$_SERVER['QUERY_STRING']);
?>