<?php
require(dirname(dirname(dirname(__FILE__))).'/conf/Report.class.php');
$report = new Report('install 3rd party tool: PHPiCalendar');

if (file_exists('phpicalendar/')) {
	trigger_error("phpicalendar already installed", E_USER_WARNING);
	exit;
}

require('../../lib/uncompress.php');
// PHP unit testing framework

$repos_package = "phpicalendar";
$home = "phpicalendar.sourceforge.net";

$version = "2.22";
$archive = "http://heanet.dl.sourceforge.net/sourceforge/phpicalendar/phpicalendar-$version.tgz";

$basedir = dirname(__FILE__);
$dir_backslash = rtrim($basedir, DIRECTORY_SEPARATOR);
$dir = str_replace('\\', '/', $dir_backslash);
$extracted_folder = "$dir/$repos_package-$version";

/*
	extract GZ archive
	arg 1 is an absolute path to a gz archive
	arg 2 is the extracted file's name
	arg 3 is optional. default value is 1 000 000 000. it has to be larger than the extracted file 
*/
uncompressGZ($archive, $extracted_folder.".tar", 2000000 );

$filename = $extracted_folder.".tar";

/*
	extract TAR archive
	arg 1 is an absolute path to a gz archive
	arg 2 is the extracted file's name. it is optional. default value is the same path as the tar file
	arg 3 is optional. it should be used only if a special directory from the tar file is needed.  
*/
uncompressTAR( $filename, null, null );

deleteFile($filename);  // delete the tar file

// get everything in place
$destination = $dir.'/phpicalendar/';
rename($extracted_folder.'/phpicalendar', $destination);

$configfile = $destination.'config.inc.php';
if (!file_exists($configfile)) trigger_error("Could not find config file $configfile in extracted contents.", E_USER_ERROR);
$fh = fopen($configfile, 'a');
fwrite($hf, "<?php
// *** repos ***
require_once(dirname(dirname(__FILE__)).'/phpicalendar.inc.php');
// *************
?>");
fclose($fh);

deleteFolder($extracted_folder);

$report->display();
?>

