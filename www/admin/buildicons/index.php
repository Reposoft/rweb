<?php
/**
 * Convert raw icons to web png.
 *
 * @package admin
 */

require('../../conf/Presentation.class.php');
require('../../lib/imagemagick/convert.inc.php');

require('../admin.inc.php'); // for getDirContents

define('BUILDICONS_DENSITY', '72x72');
define('BUILDICONS_DEPTH', '24');

$p = new Presentation();

$convert = convertGetCommand();
$convert = trim($convert, '"');

exec("$convert --version", $output, $return);
if ($return) trigger_error("Invalid command: $convert --version", E_USER_ERROR);

$p->assign('imagemagick', $output[0]);

function isImage($filename) {
	return strEnds($filename, '.png') || strEnds($filename, '.gif');
}

if (isset($_REQUEST[SUBMIT])) {
	
	$folder = rtrim(rawurldecode($_REQUEST['folder']), '/');
	if (!file_exists($folder)) trigger_error("Originals folder {$folder} does not exist", E_USER_ERROR);
	$p->assign('folder', $folder);
	
	$size = $_REQUEST['size'];
	if (!is_numeric($size)) trigger_error("Size '$size' is not an integer.'");
	$p->assign('size', $size);
	
	$background = $_REQUEST['background'];
	if (!strBegins($background, '#')) trigger_error("Background color '$background' does not begin with #.'");
	$p->assign('background', $background);
	
	// all the originals
	$originals = getDirContents($folder);
	$p->assign('folderContents', $originals);
	$originals = array_filter($originals, 'isImage');
	
	// where to place the new images
	$destination = toPath("$folder/converted");
	mkdir($destination);
	$p->assign('destination', $destination);
	
	// use one color background image
	$backgroundFile = "$destination/overlaybackground.png";
	createBackgroundImage($backgroundFile, $size, $background);
	
	// convert all
	foreach($originals as $original) {
		$outFile = "$destination/$original";
		$out = convertAndFlatten("$folder/$original", $backgroundFile, $size, $outFile);
		if ($out) {
			$p->append('error', implode("\n", $out));
			continue;
		}
		$filesize = filesize($outFile);
		convertToIndexedColor($outFile, 64);
		$indexed = filesize($outFile);
		$p->append('converted', array('file'=>$original, 'size'=>$filesize, 'indexed'=>$indexed));
	}
	$p->display();
} else {
	// default values
	$p->assign('size', 24);
	$p->assign('background', '#ECF1EF');
	// show form
	$p->display();
}

function createBackgroundImage($file, $size, $color) {
	global $convert;
	if (System::isWindows()) $file = strtr($file, '/','\\');
	$cmd = "$convert "
		." -density ".BUILDICONS_DENSITY
		." -depth ".BUILDICONS_DEPTH
		." -size {$size}x{$size}"
		." xc:{$color} \"$file\"";
	exec($cmd, $out, $return);
	if ($return) trigger_error('Could not create background image with command: '.$cmd, E_USER_ERROR);
	return $out;
}

function convertAndFlatten($file, $backgroundFile, $size, $destinationFile) {
	global $convert;
	if (System::isWindows()) $backgroundFile = strtr($backgroundFile, '/','\\');
	if (System::isWindows()) $destinationFile = strtr($destinationFile, '/','\\');
	$cmd = "$convert \"$backgroundFile\" \"$file\""
		." -gravity center"
		." -filter blackman"
		." -support 0.8"
		." -resize {$size}x{$size}"
		." -density ".BUILDICONS_DENSITY
		." -depth ".BUILDICONS_DEPTH
		." -composite \"$destinationFile\"";
	exec($cmd, $out, $return);
	return $out;
}

function convertToIndexedColor($destinationFile, $colors) {
	global $convert;
	if (System::isWindows()) $destinationFile = strtr($destinationFile, '/','\\');
	$tmp = "$destinationFile.tmp";
	$cmd = "$convert \"$destinationFile\" -colors $colors \"$tmp\"";
	exec($cmd, $out, $return);
	if ($return) trigger_error('Convert to index color failed: '.implode("\n", $out));
	if (file_exists($tmp)) {
		unlink($destinationFile);
		rename($tmp, $destinationFile);
		return true;
	} else {
		return false;
	}
}

?>
