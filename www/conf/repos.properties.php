<?php
// Repos properties as php variables
$repos_config = parse_ini_file( dirname(__FILE__) . '/repos.properties', false );

/**
 * Config value getter
 * @param key the configuration value key, as in repos.properties
 * @return the value corresponding to the specified key. False if key not defined.
 */ 
function getConfig($key) {
	if (isset($repos_config[$key]))
		return (repos_config[$key] );
	return false;
}

// Extra runtime information
function isWindows() {
	return ( substr(PHP_OS, 0, 3) == 'WIN' );
}

function isTestRun() {
	global $argv;
	return ( $argv[1] == 'unitTest' || isset($_GET['unitTest']) );
}



?>