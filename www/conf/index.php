<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Untitled Document</title>
</head>

<body>
<?php
/**
 * Debug and visualize repos configuration
 */

// default configuration includes, the way they should be referenced in php files
require( dirname(__FILE__) . '/authentication.inc.php' );
require( dirname(__FILE__) . '/repos.properties.php' );

// configuration index settings
$sections = array(
	'links' => 'Links',
	'debug' => 'Debug info'
	);
$links = array(
	'logout.php' => 'Log out',
	'configuration.php' => 'Configuration help'
	);
	
sections();

// --- layout ---
function sections() {
	global $sections;
	foreach ( $sections as $fnc=>$name ) {
		echo "<h2>$name</h2>\n";
		call_user_func ($fnc);
	}
}

// --- helper functions ---

/**
 * Check that path exists
 */
function checkPath($path) {

}


// --- sections' presentation ---

function links() {
	global $links;
	echo "<p>";
	foreach ( $links as $url=>$name ) {
		echo "<a href=\"$url\">$name</a> &nbsp; ";
	}
	echo "</p>\n";
}

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
	global $repos_config;
	print_r($repos_config);
	echo "\n==== Server variables ===\n";
	print_r($_SERVER);
	echo "</pre>\n";
}

?>
</body>
</html>