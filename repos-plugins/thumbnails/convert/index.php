<?php
/**
 *
 *
 * @package
 */

require('../../reposweb.inc.php');
require(ReposWeb.'open/SvnOpenFile.class.php');
require('./convert.inc.php');
require('./graphicstransforms.inc.php');
reposCustomizationInclude('transforms/graphics.php');

// create the option string to use with convert command
function getThumbnailCommand($transform, $format='', $target='-') {
	$maxWidth = $transform->getWidth();
	$maxHeight =  $transform->getHeight();
	// todo select -filter?
	// ImageMagick
	//if ($format) $format.=':'; // odd but this is how it was in r3111
	//return "$format- -thumbnail {$maxWidth}x{$maxHeight}\">\" -quality 60 -background white -flatten jpg:\"$target\"";
	// GraphicsMagick, don't specify format because it won't work with EPS
	return "-size {$maxWidth}x{$maxHeight} -geometry {$maxWidth}x{$maxHeight} -quality 75 - \"$target\"";
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

// revision number logic, rev is the old "implicit peg"
$revIsPeg = true;
$revField = 'rev';
// simplified logic that supports only p OR r
if (isset($_REQUEST['p'])) {
	if (isset($_REQUEST['rev']) || isset($_REQUEST['r'])) trigger_error('only one revision type accepted', E_USER_ERROR);
	$revField = 'p';
}
if (isset($_REQUEST['r'])) {
	if (isset($_REQUEST['rev']) || isset($_REQUEST['p'])) trigger_error('only one revision type accepted', E_USER_ERROR);
	$revIsPeg = false;
	$revField = 'r';
}

$r = new RevisionRule($revField);
// Validation::expect('rev'); // explicit revision number required for caching
if (!$r->getValue()) {
	//handleError(412, "Revision number required ".$r->getValue());
} else {
	// enable caching, see below
}

// first get the data about the repository image
$file = new SvnOpenFile(getTarget(), $r->getValue(), true, $revIsPeg);

// verify that the source can be accessed
// note that this requires host-wide login, not only /repos-web/, now that this runs in /repos-plugins
if ($file->getStatus() != 200) {
	handleError($file->getStatus(), "Could not read ".$file->getPath()." ".$r->getValue());
}

// start processing by getting the selected transform
$tf = isset($_REQUEST['tf']) ? $_REQUEST['tf'] : 'default';
if (!isset($reposGraphicsTransforms[$tf])) {
	trigger_error("Unknown graphics transform: $tf", E_USER_ERROR);
}
$transformClass = $reposGraphicsTransforms[$tf];
$transform = new $transformClass($file);
if (!$transform->getWidth()) {
	trigger_error("Graphics transform $gt is invalid, width not set", E_USER_ERROR);
}
if (!$transform->getHeight()) {
	trigger_error("Graphics transform $gt is invalid, height not set", E_USER_ERROR);
}
$thumbtype = $transform->getOutputFormat();

// needed for some old code
$extension = $file->getExtension();

// Originals could be large so we should avoid local storage if possible, but need
// alternative flow for convert that fails with stdin, such as when ralcgm is used 
$temporg = false;
if ($extension == 'cgm') {
	false : System::getTempFile('thumb', '.'.$extension);
}

// thumbnails are small, so we can store them on disc
$tempfile = System::getTempFile('thumb', '.'.$thumbtype);

// create the ImageMagick/GraphicsMagick command
$convert = $convert . ' ' . getThumbnailCommand($transform, $extension, $tempfile);

$o = new SvnOpen($temporg ? 'export' : 'cat');
//$o->addArgOption('-r', $rev);
//$o->addArgUrl(getTargetUrl());
// stricter, based on results from svn info, produces unique key with url@last-changed-rev
$rev = $file->getRevisionLastChanged();
$urlForPeg = rawurldecode($file->file['url']); // getUrl is urlRequested
$o->addArgUrlPeg($urlForPeg, $rev); // $rev is a peg revision
if ($temporg) {
	$o->addArgPath($temporg);
} else {
	$o->addArgOption('|', $convert, false);
}

if($o->exec()) {
	handleError($o->getExitcode(), implode('"\n"', $o->getOutput()));
}

if ($temporg) {
	$offlineconvert = preg_replace('/([ :])- /', '$1"'.$temporg.'" ', $convert);
	$c = new Command($offlineconvert, false);
	if ($c->exec()) {
		handleError($c->getExitcode(), implode('"\n"', $c->getOutput()), 1);
	}
	unlink($temporg);
}

// it might happen that convert exits with code 0 but the thumbnail is not created
if (!file_exists($tempfile) || !filesize($tempfile)) {
	// then it might be because the source has multiple pages
	// in which case we create each page as a separate thumb
	// (this is quite inefficient but I found no way to restrict to first page when using stdin as source)
	if (file_exists("$tempfile.0")) {
		// delete the others
		System::deleteFile("$tempfile");
		for ($p = 1; file_exists("$tempfile.$p"); $p++) {
			System::deleteFile("$tempfile.$p");
		}
		$tempfile = "$tempfile.0";
	} else {
		// otherwise show output from command (which is probably empty)
		handleError(0, implode('"\n"', $o->getOutput()));
	}
}

// thumbnails can be cached permanently if target and revsion number is in the url, caches should be user-private
if ($r->getValue()) {
	header('Cache-Control: private, max-age=8640000');
} else {
	header('Cache-Control: private');
}

// send from the tempfile
showImage($tempfile, $thumbtype);

System::deleteFile($tempfile);

function showImage($file, $type='jpeg') {
	$size = filesize($file);
	header('Content-Type: image/'.$type);
	header('Content-Length: '.$size);
	$f = fopen($file, 'rb');
	fpassthru($f);
	fclose($f);	
}

function handleError($code, $message, $image='error.jpg') {
	// for viewing the error using copy image location + curl or paste in new tab
	if (!getHttpReferer()) {
		header('Content-Type: text/plain');
		echo "\n=== Convert error, code $code ===\n";
		echo $message;
		exit;
	}
	// error image to user
	$tempfile = dirname(__FILE__).'/'.$image;
	showImage($tempfile);
	exit;
}

?>
