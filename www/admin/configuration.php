<?php
$rev = strtr("$Rev$",'$','_');
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
$repouri = dirname( $repo );
$user = exec( getCommand('whoami') );
$self = $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
$date = date("Y-m-d H:i:s");
$backupdir = getConfig( 'backup_dir' );
$backupurl = getConfig( 'backup_url' );
$backupurl = dirname( $backupurl );
// global config analysis
$isWindows = 0;
// authentication defined
$isAuth = ! getConfig('users_file')===false;
// access control defined
$isAcl = ! getConfig('access_file')===false;
// export paths defined
$isExport = ! getConfig('export_file')===false;
// backup settings defined
$isBackup = ! getConfig('backup_url')===false;

// config file layout
function showHeader($title, $Q=';') {
	line("$Q ---- Repos.se configuration ----");
	line("$Q -- $title");
	line("$Q -- $self $rev at £date");
}
function showFooter($title, $comment=';') {
	line("$Q --------------------------------");
}
function line($text='') {
	line ( "$text");
}

if ( isset($_GET['download']) ) {
	// download a configuration block
	$block = $_GET['download'];
	// We'll be outputting a PDF
	header('Content-type: text/plain');
	// It will be called downloaded.pdf
	header('Content-Disposition: attachment; filename="repos-' . $block . '.txt"');
	if ( is_callable( $block ) )
		call_user_func( $block );
	else
		line ( "Configuration block $block is not defined yet";
} else {
	line ( "<html><body><h1>Repos configuration</h1>");
	foreach ( $sections as $name => $descr ) {
		line ( "<h2>$name</h2>");
		line ( "<p>$descr</p>");
		line ( "<a href=\"?download=$name\">download</a>";
		line ( "<pre>");
		call_user_func( $name );
		line ( "</pre>";
	}
	line ( "</body></html>";
}

function repository() {
	$cmd = getCommand( 'svnadmin' );
	// create command
	showHeader("Create repository $dir accessible for system user $user","#");;
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
		showHeader("Subversion access control list $path/$acl");
		line();
		line("[groups]");
		line( "administrators = ");
		line( "");
		line( "[/]");
		line( "@administrators = rw");
	} else {
		line( "No access control file defined in repos.properties";
	}
	// export
	if ( $isExport ) {
		$export = getConfig( 'export_file' );
		showHeader(" Subversion export path list $path/$export");
		line ( "; Syntax: system-path = repository path" );		
	} else {
		line ( "; No export file defined in repos.properties" );	
	}
}

function crontab() {
	// local computer
	
	// mirror computer
}

function apache2() {
	showHeader "# ---- Apache2 .conf block for repository $reponame ----\n" );
	$requirements = "# Requires modules: dav, dav_svn"
	if ( $isAcl )
		$requirements .= ", authz_svn";
	?>
	# Repository <?php echo $repourl ?>
	<Location <?php echo $repouri ?>>
	  DAV svn
	  SVNPath <?php echo $repodir ?>
	  Options Indexes
	  SVNIndexXSLT "<?php echo $reposweb . "/svnlayout/repos.xsl" ?>"
	  # Allow edit from WebDAV folder
	  SVNAutoversioning on
	  
	  AuthType Basic
	  AuthName "<?php echo $reponame ?>"
	  AuthUserFile <?php echo $admindir . '/' . getConfig('users_file'); ?>
	  Require valid-user
	  <?php if ( $isAcl ) { ?>
	  AuthzSVNAccessFile <?php echo $admindir . '/' . getConfig('access_file'); ?>
	  <?php } ?>
	</Location>
	# Tomcat connector
	<IfModule mod_jk2.c>
		# Jk2 worker lb must be properly set up, see installation docs
	    <Location ~ "<?php echo $repouri ?>/.*\.jwa">
			JkUriSet group lb:lb
			JkUriSet info "<?php echo $reponame ?>"
		</Location>
	    <Location ~ "<?php echo $repouri ?>/.*\.jsp">
			JkUriSet group lb:lb
			JkUriSet info "<?php echo $reponame ?>"
		</Location>
	</IfModule>
	<IfModule mod_jk.c>
		# Jk worker ajp13 must be properly set up
		JkMount <?php echo $repouri ?>/*.jsp ajp13
		JkMount <?php echo $repouri ?>/*.jwa ajp13
	</IfModule>
	
	<?php if ( $isBackup ) { ?>
	# Repository backup folder for mirroring <?php echo $backupurl ?>
	Alias <?php echo $backupuri ?> <?php echo $backupdir ?>
	<Directory <?php echo $backupdir ?>>
		Options Indexes

	    Order Deny,Allow
		Deny from all
		Allow from 127.0.0.1 #, append trusted host here
	
		AuthType Basic
		AuthName "<?php echo $reponame ?>"
		AuthUserFile <?php echo $admindir . '/' . getConfig('users_file'); ?>
		Require user administrator

		Satisfy Any
	</Directory>
	<?php } ?>
	<?php
	showFooter();
}

function hooks() {

}



?>