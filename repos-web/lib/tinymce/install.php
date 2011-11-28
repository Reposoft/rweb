﻿<?PHP
require(dirname(dirname(dirname(__FILE__))).'/conf/Report.class.php');
require '../uncompress.php';
$report = new Report('Install TinyMCE');


// TinyMCE for repos html editor plugin

$repos_package = "tinymce";
$home = "http://tinymce.moxiecode.com/";

$version = "3_2_7";
$archive = "http://downloads.sourceforge.net/project/tinymce/TinyMCE/3.2.7/tinymce_${version}_jquery.zip";
$license = "http://wiki.moxiecode.com/index.php/TinyMCE:License";

$basedir = dirname(__FILE__);
$dir = strtr($basedir, "\\", '/');
$tmp = $dir.'/downloaded.tmp';
$extracted_folder = "$dir/$repos_package/";

if (file_exists($extracted_folder)) {
	$report->ok("$repos_package.' is already installed, done.");
	$report->display();
	exit;
}

if(download($archive, $tmp)) {
	$report->info("Download complete.");
} else {
	$report->fatal("Download failed.");
}

decompressZip($tmp, $dir);

System::deleteFile($tmp);

$version = "2_0_2";
$archive = "http://downloads.sourceforge.net/tinymce/tinymce_compressor_php_$version.zip?download";
$license = "http://wiki.moxiecode.com/index.php/TinyMCE:License";

$basedir = dirname(__FILE__).'/tinymce/jscripts/tiny_mce';
$dir = strtr($basedir, "\\", '/');
$tmp = $dir.'/downloaded_compressor.tmp';
$extracted_folder = "$dir/";

if(download($archive, $tmp)) $report->info("Download complete.");

decompressZip($tmp, $dir);

if (!copy("$dir/tinymce_compressor_php/tiny_mce_gzip.js", "$dir/tiny_mce_gzip.js")) {
	$report->warn("Failed to copy $dir/tinymce_compressor_php/tiny_mce_gzip.js to $dir. You should do it manually.");
} else {
	$report->info("Successfully copied $dir/tinymce_compressor_php/tiny_mce_gzip.js to $dir.");
}

if (!copy("$dir/tinymce_compressor_php/tiny_mce_gzip.php", "$dir/tiny_mce_gzip.php")) {
	$report->warn("Failed to copy $dir/tinymce_compressor_php/tiny_mce_gzip.php to $dir. You should do it manually.");
} else {
	$report->info("Successfully copied $dir/tinymce_compressor_php/tiny_mce_gzip.php to $dir.");
}

System::deleteFile($tmp);
$report->ok("Done.");
$report->display();
?>