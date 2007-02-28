<?php
/**
 *
 *
 * @package
 */

/*
convert -size 150x150 test.jpg \
-strip -coalesce -resize 150x150 \
-quality 100 test.thumb.jpg

And it can be further improved by replacing the -strip and -resize with
-thumbnail which does both.
*/
require('../SvnOpenFile.class.php');
require('../../lib/imagemagick/convert.inc.php');
define('THUMB_SIZE', 150);

function getThumbnailCommand($format='', $target='-') {
	$z = THUMB_SIZE;
	if ($format) $format.=':';
	$convert = convertGetCommand();
	// todo select -filter?
	return "$convert $format- -thumbnail {$z}x{$z}\">\" -quality 60 jpg:\"$target\"";
}

// first get the data about the repository image
$r = new RevisionRule();
$file = new SvnOpenFile(getTarget(), $r->getValue());
$extension = $file->getExtension();

// verify that the source can be accessed
if ($file->getStatus() != 200) {
	handleError($file->getStatus(), "Could not read ".$file->getPath()." ".$r->getValue());
}

// thumbnails are small, so we can store them on disc
$tempfile = System::getTempFile('thumb');

// create the ImageMagick command
$convert = getThumbnailCommand($extension, $tempfile);

// integer revision number, can be cached
$rev = $file->getRevision();

$o = new SvnOpen('cat');
$o->addArgOption('-r', $rev);
$o->addArgUrl(getTargetUrl());
$o->addArgOption('|', $convert, false);
if($o->passthru()) {
	handleError($o->getExitcode(), implode('"\n"', $o->getOutput()));
}

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

function handleError($code, $message) {
	// for viewing the error in new tab
	if (!getHttpReferer()) {
		header('Content-Type: text/plain');
		echo "\n=== Convert error, code $code ===\n";
		echo $message;
		exit;
	}
	// error image to user
	$tempfile = dirname(__FILE__).'/error.jpg';
	showJpeg($tempfile);
	exit;
}

?>
