<?php
// default configuration includes, the way they should be referenced in php files
require( dirname(__FILE__) . '/authentication.inc.php' );
require( dirname(__FILE__) . '/repos.properties.php' );

// page contents
$links = array(
	'index.php' => 'Check configuration'
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
$isAuth = isset($repos_config['users_file']);
// access control defined
$isAcl = isset($repos_config['access_file']);

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