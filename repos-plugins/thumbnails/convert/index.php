<?php
/**
 *
 *
 * @package
 */

require('../../reposweb.inc.php');
require(ReposWeb.'open/SvnOpenFile.class.php');
require('./convert.inc.php');
define('THUMB_SIZE', 150);

// create the option string to use with convert command
function getThumbnailCommand($format='', $target='-') {
	$z = THUMB_SIZE;
	if ($format) $format.=':';
	// todo select -filter?
	// ImageMagick
	//return "$format- -thumbnail {$z}x{$z}\">\" -quality 60 -background white -flatten jpg:\"$target\"";
	// GraphicsMagick
	return "-size {$z}x{$z} -geometry {$z}x{$z} -quality 75 - \"$target\"";
}

// verify that the graphics tool exists
$convert = convertGetCommand();

exec("$convert -version", $output, $result);
if ($result > 1) {
	//echo($result);print_r($output);
	handleError('[convert not installed]','','empty.jpg');
}

// can't be sure that the browser automatically forwards credentials to this plugin folder
// TODO make sure login is not required for public readable images
targetLogin();

// first get the data about the repository image
$r = new RevisionRule();
// Validation::expect('rev'); // explicit revision number required for caching
if (!$r->getValue()) {
	//handleError(412, "Revision number required ".$r->getValue());
} else {
	// enable caching, see below
}

$file = new SvnOpenFile(getTarget(), $r->getValue());
$extension = $file->getExtension();

// verify that the source can be accessed
// note that this requires host-wide login, not only /repos-web/, now that this runs in /repos-plugins
if ($file->getStatus() != 200) {
	handleError($file->getStatus(), "Could not read ".$file->getPath()." ".$r->getValue());
}

// thumbnails are small, so we can store them on disc
$tempfile = System::getTempFile('thumb');

// create the ImageMagick command
$convert = $convert . ' ' . getThumbnailCommand($extension, $tempfile);

// integer revision number, can be cached
$rev = $file->getRevision();

$o = new SvnOpen('cat');
$o->addArgOption('-r', $rev);
$o->addArgUrl(getTargetUrl());
$o->addArgOption('|', $convert, false);
if($o->exec()) {
	handleError($o->getExitcode(), implode('"\n"', $o->getOutput()));
}

// thumbnails can be cached permanently if target and revsion number is in the url
if ($r->getValue()) {
	header('Cache-Control: max-age=8640000');
}

// send from the tempfile
showJpeg($tempfile);

System::deleteFile($tempfile);

function showJpeg($file) {
	$size = filesize($file);
	header('Content-Type: image/jpeg');
	header('Content-Length: '.$size);
	$f = fopen($file, 'rb');
	fpassthru($f);
	fclose($f);	
}

function handleError($code, $message, $image='error.jpg') {
	// for viewing the error in new tab
	if (!getHttpReferer()) {
		header('Content-Type: text/plain');
		echo "\n=== Convert error, code $code ===\n";
		echo $message;
		exit;
	}
	// error image to user
	$tempfile = dirname(__FILE__).'/'.$image;
	showJpeg($tempfile);
	exit;
}

?>
