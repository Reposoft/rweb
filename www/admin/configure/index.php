<?php
// svn propset svn:keywords "Rev" configuration.php
$rev = strtr("$Rev$",'$',' ');
require("../../conf/repos.properties.php" );

// page contents
$links = array(
	'index.php' => 'Administration menu',
	'../conf/index.php' => 'Check configuration'
	);
$sections = array(
	'repository' => 'How to create the repository',
	'administration' => 'Local files needed for administration of the repository',
	'apache2' => 'Web server configuration directives',
	'crontab' => 'Crontab or scheduler commands needed for backup',
	//'hooks' => 'Hook scripts for event-driven repository maintenance',
	//'ie_png' => 'Enable 24-bit PNG transparency in IE for basic SVN layout theme'
	);

// helper variables
$repourl = getRepository();
$reponame = $repourl; // getConfig( 'repo_name' ) no loger used, minimizing config dependence.
$repodir = getConfig( 'local_path' );
$reposweb = getWebapp();
$admindir = getConfig( 'admin_folder' );
$repouri = ereg_replace("[[:alpha:]]+://[^-/<>[:space:]]+[[:alnum:]/]","/", $repourl);
$user = exec( getCommand('whoami') );
$self = $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
$date = date("Y-m-d H:i:s");
$backupdir = getConfig( 'backup_folder' );
$backupurl = getConfig( 'backup_url' );
$backupuri = ereg_replace("[[:alpha:]]+://[^/<>[:space:]]+[[:alnum:]/]","/", $backupurl);
// authentication defined
$isAuth = ! getConfig('users_file')===false;
// access control defined
$isAcl = ! getConfig('access_file')===false;
// export paths defined
$isExport = ! getConfig('export_file')===false;
// backup settings defined
$isBackup = ! getConfig('backup_url')===false;

global $reponame, $repourl, $repodir, $reposweb, $admindir, $repouri, $user, $self, $date, $backupdir, $backupurl, $backupuri, $rev, $isAuth, $isAcl, $isExport, $isBackup;

// config file layout
function showHeader($title, $Q=';') {
	global $reponame, $repourl, $repodir, $reposweb, $admindir, $repouri, $user, $self, $date, $backupdir, $backupurl, $backupuri, $rev, $isAuth, $isAcl, $isExport, $isBackup;
	line("$Q ---- Repos.se configuration ----" );
	line("$Q -- $title" );
	line("$Q -- $self $rev at $date" );
}
function showFooter($Q=';') {
	line("$Q --------------------------------" );
}
function line($text='') {
	echo $text.getNewline();
}

if ( isset($_GET['download']) ) {
	// download a configuration block
	$block = $_GET['download'];
	// Send textfile to user
	header('Content-type: text/plain');
	// It will be called downloaded.pdf
	header('Content-Disposition: attachment; filename="repos-' . $block . '.txt"');
	if ( is_callable( $block ) )
		call_user_func( $block );
	else
		line( "Configuration block $block is not defined yet" );
} else {
	$title = "Repos configuration"?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title><?php echo $title ?></title>
	<link href="../../style/global.css" rel="stylesheet" type="text/css">
	<link href="../../style/docs.css" rel="stylesheet" type="text/css">
	</head>
	
	<body>
	<h2>Repository set up</h2>
	<p>based on the current <a href="../../conf/">repos.properties</a> configuration.</p>
	<?php
	line( "<ul>");
	foreach ( $sections as $name => $descr ) {
		line( "<li><a href=\"#$name\">$descr</a></li>" );
	}
	line( "</ul>");
	foreach ( $sections as $name => $descr ) {
		line( "<a name=\"$name\"></a><h2>$name</h2>" );
		line( "<p>$descr</p>" );
		line( "<a href=\"?download=$name\">download</a>" );
		line( "<pre>" );
		call_user_func( $name );
		line( "</pre>" );
	}
	line( "</body></html>" );
}

function repository() {
    global $repodir;
	$cmd = getCommand( 'svnadmin' );
	$user = 'administrator';
	// create command
	showHeader("Create repository $repodir accessible for system user $user","#" );
	line( "mkdir $repodir" );
	line( "$cmd create $repodir" );
	if ( ! isWindows() ) {
		$groups = array();
		exec( 'groups', $groups );
		if ( ! isset($groups[0]) )
			break;
		line( "# Setting permissions for unix-like system, $user's primary group is $groups[0]" );
		line( "chgrp -R $groups[0] $repodir" );
		line( "chmod -R g+rw $repodir" );
	}
	// set permissions
}

function administration() {
	global $isAcl, $isExport, $path;
	if ( $isAcl ) {
		// authorization
		$acl = getConfig('access_file');
		showHeader("Subversion access control list $path/$acl" );
		line();
		line("[groups]" );
		line( "administrators = " );
		line( "" );
		line( "[/]" );
		line( "@administrators = rw" );
	} else {
		line( "No access control file defined in repos.properties" );
	}
	// export
	if ( $isExport ) {
		$export = getConfig( 'export_file' );
		showHeader(" Subversion export path list $path/$export" );
		line( "; Syntax: system-path = repository path" );		
	} else {
		line( "; No export file defined in repos.properties" );	
	}
}

function crontab() {
	// local computer
	
	// mirror computer
}

function apache2() {
	global $reponame, $repourl, $repodir, $reposweb, $admindir, $repouri, $user, $self, $date, $backupdir, $backupurl, $backupuri, $rev, $isAuth, $isAcl, $isExport, $isBackup;
	showHeader( "Apache2 .conf block for repository $reponame", "#" );
	$requirements = "# Requires modules: dav, dav_svn";
	if ( $isAcl )
		$requirements .= ", authz_svn";
	?>
	# Repository <?php echo $repourl ?> 
	&lt;Location <?php echo $repouri ?>&gt;
	  DAV svn
	  SVNPath <?php echo $repodir ?> 
	  Options Indexes
	  # Layout file, "<?php echo $reposweb . "view/repos.xsl" ?>" or:
	  SVNIndexXSLT "<?php echo "/repos/view/repos.xsl" ?>"
	  # Allow edit from WebDAV folder
	  SVNAutoversioning on
	  
	  AuthType Basic
	  # AuthName should be the repository root URL (as a single-signon standard)
	  AuthName "<?php echo $reponame ?>"
	  AuthUserFile <?php echo $admindir . getConfig('users_file'); ?> 
	  Require valid-user
	  <?php if ( $isAcl ) { ?> 
	  AuthzSVNAccessFile <?php echo $admindir . getConfig('access_file'); ?> 
	  <?php } ?> 
	&lt;/Location&gt;
	
	<?php if ( $isBackup ) { ?>
	# Repository backup folder for mirroring <?php echo $backupurl ?> 
	Alias <?php echo $backupuri ?> <?php echo $backupdir ?> 
	&lt;Directory <?php echo $backupdir ?>&gt;
		Options Indexes

	    Order Deny,Allow
		Deny from all
		Allow from 127.0.0.1 #, append trusted host here
	
		AuthType Basic
		AuthName "<?php echo $reponame ?>"
		AuthUserFile <?php echo $admindir . getConfig('users_file'); ?> 
		Require user administrator

		Satisfy Any
	&lt;/Directory&gt;
	<?php } 
	showFooter("#");
}

?>
