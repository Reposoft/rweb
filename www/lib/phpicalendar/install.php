<?php

require(dirname(dirname(dirname(__FILE__))).'/conf/Report.class.php');
$report = new Report('install 3rd party tool: PHPiCalendar');

if (file_exists('phpicalendar/')) {
	$report->ok("phpicalendar already installed, done.");
	$report->display();
	exit;
}

require('../../lib/uncompress.php');

$repos_package = "phpicalendar";
$home = "phpicalendar.sourceforge.net";

$version = "2.22";
$archive = "http://downloads.sourceforge.net/phpicalendar/phpicalendar-$version.zip";

$basedir = dirname(__FILE__);
$dir = strtr($basedir, "\\", '/');
$tmp = $dir.'/downloaded.tmp';

$extracted_folder = "$dir/$repos_package-$version";

if(download($archive, $tmp)) $report->info("Download complete.");

decompressZip($tmp, $dir);

System::deleteFile($tmp);

$report->info('Decompress OK.');

// get everything in place
$destination = $dir.'/phpicalendar/';
rename($extracted_folder.'/phpicalendar', $destination);

$configfile = $destination.'config.inc.php';
if (!file_exists($configfile)) trigger_error("Could not find config file $configfile in extracted contents.", E_USER_ERROR);
$fh = fopen($configfile, 'a');
fwrite($fh, "<?php
// *** repos ***
require_once(dirname(dirname(__FILE__)).'/phpicalendar.inc.php');
// *************
?>");
fclose($fh);

System::deleteFolder($extracted_folder.'/');

// delete the sample calendars bundled with phpicalendar
System::deleteFile($destination.'calendars/Home.ics');
System::deleteFile($destination.'calendars/US Holidays.ics');
System::deleteFile($destination.'calendars/Work.ics');

// untar leaves a strange @LongLink file
if (file_exists($dir.'/@LongLink')) System::deleteFile($dir.'/@LongLink');
if (file_exists($dir.'/__MACOSX')) unlink($dir.'/__MACOSX'); // very strange symlink kind of thing

$report->ok('Installed PHPiCalendar and added custom configuration, done.');

$report->display();
?>