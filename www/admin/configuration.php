<?php
// svn propset svn:keywords "Rev" configuration.php
$rev = strtr("$Rev$",'$',' ');
// default configuration includes, the way they should be referenced in php files
function upOne($dirname) { return substr($dirname, 0, strrpos(rtrim(strtr($dirname,'\\','/'),'/'),'/') ); }
//require( upOne(dirname(__FILE__)) . '/conf/authentication.inc.php' );
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

// helper variables
$reponame = getConfig( 'repo_name' );
$repourl = getConfig( 'repo_url' );
$repodir = getConfig( 'local_path' );
$reposweb = getConfig( 'repos_web' );
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
	echo "$text\n\r";
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
	$tilte = "Repos configuration"?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title><?php echo $title ?></title>
	<link href="../css/repos-standard.css" rel="stylesheet" type="text/css">
	</head>
	
	<body>
	<?php
	foreach ( $sections as $name => $descr ) {
		line( "<h2>$name</h2>" );
		line( "<p>$descr</p>" );
		line( "<a href=\"?download=$name\">download</a>" );
		line( "<pre>" );
		call_user_func( $name );
		line( "</pre>" );
	}
	line( "</body></html>" );
}

function repository() {
	$cmd = getCommand( 'svnadmin' );
	// create command
	showHeader("Create repository $dir accessible for system user $user","#" );
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
	  #Only from the specified domain: SVNIndexXSLT "<?php echo $reposweb . "/svnlayout/repos.xsl" ?>"
	  SVNIndexXSLT "<?php echo "/svnlayout/repos.xsl" ?>"
	  # Allow edit from WebDAV folder
	  SVNAutoversioning on
	  
	  AuthType Basic
	  AuthName "<?php echo $reponame ?>"
	  AuthUserFile <?php echo $admindir . '/' . getConfig('users_file'); ?> 
	  Require valid-user
	  <?php if ( $isAcl ) { ?> 
	  AuthzSVNAccessFile <?php echo $admindir . '/' . getConfig('access_file'); ?> 
	  <?php } ?> 
	&lt;/Location&gt;
	# Tomcat connector
	&lt;IfModule mod_jk2.c&gt;
		# Jk2 worker lb must be properly set up, see installation docs
		&lt;Location ~ "<?php echo $repouri ?>/.*\.jwa"&gt;
			JkUriSet group lb:lb
			JkUriSet info "<?php echo $reponame ?>"
		&lt;/Location&gt;
		&lt;Location ~ "<?php echo $repouri ?>/.*\.jsp"&gt;
			JkUriSet group lb:lb
			JkUriSet info "<?php echo $reponame ?>"
		&lt;/Location&gt;
	&lt;/IfModule&gt;
	&lt;IfModule mod_jk.c&gt;
		# Jk worker ajp13 must be properly set up
		JkMount <?php echo $repouri ?>/*.jsp ajp13
		JkMount <?php echo $repouri ?>/*.jwa ajp13
	&lt;/IfModule&gt;
	
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
		AuthUserFile <?php echo $admindir . '/' . getConfig('users_file'); ?> 
		Require user administrator

		Satisfy Any
	&lt;/Directory&gt;
	<?php } 
	showFooter("#");
}

function hooks() {

}



?>
