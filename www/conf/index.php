<?php
/**
 * Debug and visualize repos configuration
 */

// default configuration includes, the way they should be referenced in php files
require( dirname(__FILE__) . '/authentication.inc.php' );
require( dirname(__FILE__) . '/repos.properties.php' );

// configuration index settings
$links = array(
	'logout.php' => 'Log out',
	'configuration.php' => 'Configuration help'
	);
$sections = array(
	'debug' => 'Debug info'
	);
// commands that should be looked for, and the message as HTML if they don't exist
$dependencies = array(
	
	);

// Debug output, does nothing
function debug() {
	echo "<pre>\n";
	echo "==== Test retrieval of credentials ===";
	echo "\nUsername = ";
	//echo $repos_authentication['user'];
	echo getReposUser();
	echo "\nPassword = ";
	echo getReposPass();
	//echo $repos_authentication['pass'];
	echo "\nBASIC string = ";
	echo getReposAuth();
	//echo $repos_authentication['auth'];
	echo "\n";
	// Display info about current repos configuration
	echo "\n==== Configuration file ===\n";
	print_r($repos_config);
	echo "\n==== Server variables ===\n";
	print_r($_SERVER);
	echo "</pre>\n";
}

?>