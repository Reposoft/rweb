<?php
/**
 * Produces graphics transforms on the fly.
 * 
 * Uses temp files for output. Might leave temp files in case of errors.
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
	$cmd = array();
	if ($maxWidth || $maxHeight) {
		$cmd[] = "-size {$maxWidth}x{$maxHeight}";
		$cmd[] = "-geometry {$maxWidth}x{$maxHeight}";
	}
	$resolution =  $transform->getResolution();
	if ($resolution) {
		$cmd[] = "-density {$resolution}";
	}
	$cmd[] = "-quality 75";
	$cmd[] = "-";
	if ($format == 'psd') {
		$cmd[] = "-flatten";
	}
	$cmd[] = "\"$target\"";
	// todo select -filter?
	// ImageMagick
	//if ($format) $format.=':'; // odd but this is how it was in r3111
	//return "$format- -thumbnail {$maxWidth}x{$maxHeight}\">\" -quality 60 -background white -flatten jpg:\"$target\"";
	// GraphicsMagick, don't specify format because it won't work with EPS
	return join(" ", $cmd);
}

// verify that the graphics tool exists
$convert = convertGetCommand();

// handleError can print text output when an image url is read using copy-paste in browser or curl
if (!getHttpReferer()) {
	exec("$convert -version", $output, $result);
	if ($result > 1) {
		//echo($result);print_r($output);
		handleError('[convert not installed]','','empty.jpg');
	}
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
// Existence of revision argument enables caching but is not required
//if (!$r->getValue()) {
//	handleError(412, "Revision number required ".$r->getValue());
//}

if (isset($_REQUEST['accept'])) {
	$extensions = preg_split('/[,\s]+/', $_REQUEST['accept'], -1, PREG_SPLIT_NO_EMPTY);
	$targetUrl = getTargetUrl();
	$probe = '';
	do {
		$s = new ServiceRequest($targetUrl.$probe);
		$s->setSkipBody();
		$s->exec();
		if ($s->getStatus() == 200) {
			$_REQUEST['target'] .= $probe;
			break;
		}
	} while ($probe = array_shift($extensions));
}

// First get the data about the repository image
// Consider this an implementation of ReposGraphicsTransformSource
// We only use SvnOpenFile to check existence, and the operation could maybe be made faster as we don't need the info
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
$thumbtype = $transform->getOutputFormat();

// See if the source extension is supported
// In future the supported extensions may depend on transform definitions
$supportedExtensions = explode('|', 'jpg|png|gif'
		.'|bmp|eps|pdf|ps|psd|ico|svg|tif|tiff'
		.'|avi'
		.'|cgm' // users that need CGM must install delegate RalCGM for ImageMagick or GraphicsMagick
		.'|ai' // some adobe formats are actually pdf or postscript
		.'');
if (!in_array(strtolower($file->getExtension()), $supportedExtensions)) {
	// TODO use a different image? This error will be common with the new trial-and-error thumbnailing approach.
	handleError(415, $file->getExtension() . ' not a supported format', 'error.png', 'HTTP/1.1 415 Unsupported Media Type');
}

// Originals could be large so we should avoid local storage if possible, but need
// alternative flow for convert that fails with stdin, such as when ralcgm is used 
$temporg = false;
if ($file->getExtension() == 'cgm') {
	$temporg = System::getTempFile('thumb', '.'.$file->getExtension());
}

// thumbnails are small, so we can store them on disc
$tempfile = System::getTempFile('thumb', '.'.$thumbtype);

// create the ImageMagick/GraphicsMagick command
$format = strtolower($file->getExtension());
if (method_exists($transform,'getCustomCommand')) {
	$convert = $transform->getCustomCommand($format, $tempfile);
} else {
	$convert = $convert . ' ' . getThumbnailCommand($transform, $format, $tempfile);
}

$o = new SvnOpen($temporg ? 'export' : 'cat');

// Set revision, three cases
if (!$r->getValue()) {
	// 1. Revision not given, we still want revision in order to make result cacheable
	$o->addArgUrlPeg(getTargetUrl(), $file->getRevision()); // RevisionLastChanged could be before a folder copy
} else if ($revIsPeg) {
	// 2. Revision given as target and its peg rev, typically when coming from history etc
	$o->addArgUrlPeg(getTargetUrl(), $r->getValue()); // trust the caller to match target and peg rev
} else {
	// 3. Revision given as some revision of the item at a HEAD url
	$o->addArgUrl(getTargetUrl()); // Should be HEAD url
	$o->addArgOption('-r', $r->getValue());
}

// command output
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

// Page param, if >0 the file must be multi-page
$page = isset($_REQUEST['page']) && is_numeric($_REQUEST['page']) ? $_REQUEST['page'] - 1 : '0';

// it might happen that convert exits with code 0 but the thumbnail is not created
if (!file_exists($tempfile) || !filesize($tempfile)) {
	// then it might be because the source has multiple pages
	// in which case we create each page as a separate thumb
	// (this is quite inefficient but I found no way to restrict to first page when using stdin as source)
	if (file_exists("$tempfile.0")) {
		// Select page based on query param
		// Optimization idea: use the temporg concept for PDFs and when page param isset 
		if ($page && !file_exists("$tempfile.$page")) {
			handleError(404, "Page ".($page + 1)." not found");
		}
		// delete the others
		System::deleteFile("$tempfile");
		for ($p = 0; file_exists("$tempfile.$p"); $p++) {
			if ($p != $page) System::deleteFile("$tempfile.$p");
		}
		$tempfile = "$tempfile.$page";
	} else {
		// otherwise show output from command (which is probably empty)
		handleError(0, implode('"\n"', $o->getOutput()));
	}
} else {
	if ($page > 0) {
		handleError(0, "Page ".($page + 1)." was requested but transform output is single page");
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

function handleError($code, $message, $image='error.jpg', $statusline='HTTP/1.1 500 Server Error') {
	header($statusline);
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
