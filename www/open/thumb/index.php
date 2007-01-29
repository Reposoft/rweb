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
define('THUMB_SIZE', 120);

function getConvertCommand($format='', $target='-') {
	$z = THUMB_SIZE;
	if ($format) $format.=':';
	// todo select -filter?
	return "convert $format- -thumbnail {$z}x{$z} -quality 60 jpg:\"$target\"";
}

// first get the data about the repository image
$r = new RevisionRule();
$file = new SvnOpenFile(getTarget(), $r->getValue());
$extension = $file->getExtension();

// thumbnails are small, so we can store them on disc
$tempfile = System::getTempFile('thumb');

// create the ImageMagick command
$convert = getConvertCommand($extension, $tempfile);

// integer revision number, can be cached
$rev = $file->getRevision();

$o = new SvnOpen('cat');
$o->addArgOption('-r', $rev);
$o->addArgUrl(getTargetUrl());
$o->addArgOption('|', $convert, false);
if($o->passthru()) {
	echo "\nReturn code: $o->getExitcode() \n";
	exit;
	// TODO
		$errorimage = '../../style/warning.png';
		$f = fopen($errorimage, 'rb');
		fpassthru($f);
		fclose($f);
}

$size = filesize($tempfile);
header('Content-Type: image/jpeg');
header('Content-Length: '.$size);
$f = fopen($tempfile, 'rb');
fpassthru($f);
fclose($f);

System::deleteFile($tempfile);

?>
