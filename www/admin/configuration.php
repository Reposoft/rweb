<?php
// default configuration includes, the way they should be referenced in php files
function upOne($dirname) { return substr($dirname, 0, strrpos(rtrim(strtr($dirname,'\\','/'),'/'),'/') ); }
require( upOne(dirname(__FILE__)) . '/conf/authentication.inc.php' );
require( upOne(dirname(__FILE__)) . "/conf/repos.properties.php" );

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

if ( isset($_GET['download']) ) {
	// download a configuration block
	$block = $_GET['download'];
	// We'll be outputting a PDF
	header('Content-type: text/plain');
	// It will be called downloaded.pdf
	header('Content-Disposition: attachment; filename="' . $block . '.txt"');
	if ( is_callable( $block ) )
		call_user_func( $block );
	else
		echo "Configuration block $block is not defined yet";
} else {
	echo "<html><body><h1>Repos configuration</h1>\n";
	foreach ( $sections as $name => $descr ) {
		echo "<h2>$name</h2>\n";
		echo "<p>$descr</p>\n";
		echo "<a href=\"?download=$name\">download</a>";
		echo "<pre>\n";
		call_user_func( $name );
		echo "</pre>";
	}
	echo "</body></html>";
}

function repository() {
	// create command
	$dir = getConfig( 'local_path' );
	$user = exec( getCommand('whoami') );
	$cmd = getCommand( 'svnadmin' );
	echo "# Create repository $dir accessible for system user $user\n";
	echo "mkdir $dir\n";
	echo "$cmd create $dir\n";
	if ( ! isWindows() ) {
		$groups = array();
		exec( 'groups', $groups );
		if ( ! isset($groups[0]) )
			break;
		echo "# Setting permissions for unix-like system, $user's primary group is $groups[0]\n";
		echo "chgrp -R $groups[0] $dir\n";
		echo "chmod -R g+rw $dir\n";
	}
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