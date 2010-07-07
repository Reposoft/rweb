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

/**
 * @return the root url to the cache repo, should be the external url
 * so that cients can read resources directly from cache.
 * The cache repository can use caching because all URLs
 * sent to client will include the revsion number.
 */
function getThumbnailCacheRepo() {
	// TODO server setting like those read in repos.properties.php
	// return getThumbnailCacheRepoDefault();
	return false; // caching disabled
}

function getThumbnailCacheRepoDefault() {
	preg_replace('/\b\/[^\/]+/', '/repos-thumbs', getRepository(), 1);
}

// for caching
require(ReposWeb.'edit/SvnEdit.class.php');

// create the option string to use with convert command
function getThumbnailCommand($transform, $format='', $target='-') {
	$maxWidth = $transform['width'];
	$maxHeight =  $transform['height'];
	// todo select -filter?
	// ImageMagick
	//if ($format) $format.=':'; // odd but this is how it was in r3111
	//return "$format- -thumbnail {$maxWidth}x{$maxHeight}\">\" -quality 60 -background white -flatten jpg:\"$target\"";
	// GraphicsMagick
	return "-size {$maxWidth}x{$maxHeight} -geometry {$maxWidth}x{$maxHeight} -quality 75 - \"$target\"";
}

// start processing by getting the selected transform
$gt = isset($_REQUEST['gt']) ? $_REQUEST['gt'] : 'thumb';
if (!isset($reposGraphicsTransforms[$gt])) {
	trigger_error("Unknown graphics transform: $gt", E_USER_ERROR);
}
$transform = $reposGraphicsTransforms[$gt];
if (!isset($transform['width'])) {
	trigger_error("Graphics transform $gt is invalid, width not set", E_USER_ERROR);
}
if (!isset($transform['height'])) {
	trigger_error("Graphics transform $gt is invalid, height not set", E_USER_ERROR);
}

// verify that the graphics tool exists
$convert = convertGetCommand();

exec("$convert -version", $output, $result);
if ($result > 1) {
	//echo($result);print_r($output);
	handleError('[convert not installed]','','empty.jpg');
}

// Enable caching in parallell repository, set $cacheRepo = false to disable
$cacheRepo = getThumbnailCacheRepo();
if (strBegins(getSelfUrl(), $cacheRepo) && !isset($_REQUEST['target'])) {
	trigger_error('On-demand thumbnail generation not implemented yet');
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
$extension = $file->getExtension();

// verify that the source can be accessed
// note that this requires host-wide login, not only /repos-web/, now that this runs in /repos-plugins
if ($file->getStatus() != 200) {
	handleError($file->getStatus(), "Could not read ".$file->getPath()." ".$r->getValue());
}

// Look for a cached file using a naming rule
if ($cacheRepo) {
	$transformId = $gt; // this assumes that the cache repo is cleared if transform definitions change
	$revision = $file->getRevision();
	$name = $file->getFilename();
	$dot = strrpos($name, '.');
	$name = substr($name, 0, $dot).'(r'.$file->getRevision().')'.".$transformId".substr($name,$dot).'.jpg';
	$cacheTarget = getTarget().'/'.$revision.'/'.$name;
	$cacheSave = getTarget().'/repos.lock';
	$cacheUrl = $cacheRepo.$cacheTarget;
	$existing = new ServiceRequest($cacheUrl);
	if ($existing->exec() == 200) {
		header("Location: $cacheUrl");
		exit;
	}
}

// jpeg is generally smaller than png but graphicsmagick produced some invalid images for line art in jpg
$thumbtype = 'png';
if (isset($transform['type'])) { // output type can be set explicitly in transform definition
	$thumbtype = 'type';
} else if (preg_match('/^jpe?g|raw/i', $file->getExtension())) {
	$thumbtype = 'jpeg';
}

// thumbnails are small, so we can store them on disc
$tempfile = System::getTempFile('thumb', '.'.$thumbtype);

// create the ImageMagick command
$convert = $convert . ' ' . getThumbnailCommand($transform, $extension, $tempfile);

// integer revision number, can be cached
$rev = $file->getRevision();

$o = new SvnOpen('cat');
//$o->addArgOption('-r', $rev);
//$o->addArgUrl(getTargetUrl());
// stricter, based on results from svn info, produces unique key with url@last-changed-rev
$rev = $file->getRevisionLastChanged();
$urlForPeg = rawurldecode($file->file['url']); // getUrl is urlRequested
$o->addArgUrlPeg($urlForPeg, $rev); // $rev is a peg revision
$o->addArgOption('|', $convert, false);
if($o->exec()) {
	handleError($o->getExitcode(), implode('"\n"', $o->getOutput()));
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

// thumbnails can be cached permanently if target and revsion number is in the url
if ($r->getValue()) {
	header('Cache-Control: private, max-age=8640000');
} else {
	header('Cache-Control: private');
}

// send from the tempfile
showImage($tempfile, $thumbtype);

// done displaying thumbnail, store in cache if enabled
if ($cacheRepo) {
	$import = new SvnEdit('import');
	$import->addArgPath($tempfile);
	$import->addArgUrl($cacheUrl);
	$import->execNoDisplay();
}

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
	// for viewing the error in new tab
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
