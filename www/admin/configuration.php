<?php
// default configuration includes, the way they should be referenced in php files
require( dirname(__FILE__) . '/authentication.inc.php' );
require( dirname(__FILE__) . '/repos.properties.php' );

// page contents
$links = array(
	'index.php' => 'Administration menu',
	'../conf/index.php' => 'Check configuration'
	);
$sections = array(
	'repository' => 'How to create the repository',
	'administration' => 'Local files needed for administration of the repository',
	'crontab' => 'Crontab or scheduler commands needed for backup',
	'apache2' => 'Web server configuration directives',
	'hooks' => 'Hook scripts for event-driven repository maintenance'
	);
// global config analysis
$isWindows = 0;
// authentication defined
$isAuth = ! getConfig('users_file')===false;
// access control defined
$isAcl = ! getConfig('access_file')===false;
// export paths defined
$isExport = ! getConfig('export_file')===false;

if (isset($_GET['download']) {
	// download a configuration block
	$block = $_GET['download'];
	// We'll be outputting a PDF
	header('Content-type: text/plain');
	// It will be called downloaded.pdf
	header('Content-Disposition: attachment; filename="' . $block . '.txt"');
	// call_user_func( "$block" ); // this is probably a security risk right now
	echo "Configuration block $block is not defined yet".
} else {
	// display all configuration blocks
}

function repository() {
	// create command
	
	// set permissions
}

function administration() {
	// authorization
	
	// export
	
	

}

function crontab() {
	// local computer
	
	// mirror computer
}

function apache2() {

}

function hooks() {

}



?>