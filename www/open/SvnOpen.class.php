<?php
/**
 * Represents a subversion read operation which does not need a working copy.
 * 
 * TODO this is a work in progress. functions are being converted from login.inc.php
 */

// use test mocks if they define global function targetLogin()
if (!function_exists('targetLogin')) require(dirname(dirname(__FILE__)).'/account/login.inc.php');

// TODO delegate command processing to Command.class.php

// *** Subversion client usage ***
define('SVN_CONFIG_DIR', _getConfigFolder().'svn-config-dir' . DIRECTORY_SEPARATOR);
if (!file_exists(SVN_CONFIG_DIR)) trigger_error('Svn config folder '.SVN_CONFIG_DIR.' does not exist. Can not run commands.', E_USER_ERROR);

/**
 * Execute svn command like the PHP exec() function
 * @param cmd The command without the SVN part, for example 'log /url/to/repo'
 * @return stdout and stderr output from the command, one array element per row. 
 *   Last element is the return code (use array_pop to remove).
 *   If returnval!=0, login_handleSvnError may be used to get error message compatible with the styleshett.
 */
function login_svnRun($cmd) {
	$svnCommand = login_getSvnSwitches().' '.$cmd;
	$result = repos_runCommand('svn', $svnCommand);
	return $result;
}

/**
 * Execute svn command like the PHP passthru() function.
 * This can not be used from a Smarty template, because it returns an integer that will be appended to the page.
 * @param cmd The command without the SVN part, for example 'log /url/to/repo'
 * @return return value of the execution.
 *   If returnval!=0, login_handleSvnError may be used to get error message compatible with the styleshett.
 */
function login_svnPassthru($cmd) {
	$svnCommand = login_getSvnSwitches().' '.$cmd;
	$returnval = repos_passthruCommand('svn', $svnCommand);
	return $returnval;
}

/**
 * Cat file(@revision) directly to result stream.
 * Currently there's no error handling on error. Ideally error handling should be compatible with XML, XHTML and <pre>-tags.
 * This function can be used from a Smarty template like: {=$targetpeg|login_svnPassthruFile}
 * @param targetUrl the resource url. must be a file, can be with a peg revision (url@revision)
 * @param revision optional, >0, the revision to read. if omitted the function reads HEAD.
 */
function login_svnPassthruFile($targetUrl, $revision=0) {
	if ($revision > 0) {
		$cmd = 'cat '.escapeArgument($targetUrl.'@'.$revision); // using "peg" revision
	} else {
		$cmd = 'cat '.escapeArgument($targetUrl);
	}
	$returnvalue = login_svnPassthru($cmd);
	if ($returnvalue) trigger_error("Could not read contents of file $targetUrl", E_USER_ERROR);
}

/**
 * Cat file(@revision) to result stream, with output escaped as html.
 * This function can be used from a Smarty template like: {=$targetpeg|login_svnPassthruFileHtml}
 * @param targetUrl the resource url. must be a file, can be with a peg revision (url@revision)
 * @param revision optional, >0, the revision to read. if omitted the function reads HEAD.
 * TODO currently this function buffers the entire file in memory, which is not nearly as efficient as login_svnPassthruFile.
 */
function login_svnPassthruFileHtml($targetUrl, $revision=0) {
	if ($revision > 0) {
		$cmd = 'cat '.escapeArgument($targetUrl.'@'.$revision); // using "peg" revision
	} else {
		$cmd = 'cat '.escapeArgument($targetUrl);
	}
	$contents = login_svnRun($cmd);
	if (array_pop($contents)) trigger_error("Could not read contents of file $targetUrl.\n".htmlspecialchars(implode("\n", $contents)), E_USER_ERROR);
	// TODO add check if the file is Latin-1, convert if nessecary, and handle the diff header (with filename) based on os
	foreach ($contents as $c) echo(htmlspecialchars($c)."\n");
}

/**
 * Returns the mime type for a file in the repository.
 * If revision is HEAD (which it is when the second argument is omited)
 * the mime type is read from the HTTP header of the repository resource. That gives us defaults
 * based on filename extension, without the need to maintain our ow list.
 * If revision number is not head, login_getMimeTypeProperty is used.
 * @param targetUrl the file
 * @param revision, optional revision number, if not HEAD
 * @return the mime type string, or false if unknown (suggesting application/x-unknown)
 */
function login_getMimeType($targetUrl, $revision='HEAD') {
	if ($revision!='HEAD') {
		return login_getMimeTypeProperty($targetUrl, $revision);
	}
	$headers = getHttpHeaders($targetUrl, getReposUser(), _getReposPass());
	if (!isset($headers['Content-Type'])) trigger_error("Could not get content type for target $targetUrl");
	$c = $headers['Content-Type'];
	if (strContains($c, ';')) return substr($c, 0, strpos($c, ';'));
	return $c;
}

/**
 * Returns the value of the svn:mime-type property of a file with revision number.
 *
 * @param String $targetUrl the file url
 * @param String $revision the revision number, integer or HEAD
 * @return String mime type, or false if property not set.
 */
function login_getMimeTypeProperty($targetUrl, $revision) {
	$url = $targetUrl.'@'.$revision;
	$cmd = 'propget svn:mime-type '.escapeArgument($url);
	$result = login_svnRun($cmd);
	if (array_pop($result)) trigger_error("Could not find the file '$targetUrl' revision $revision in the repository.", E_USER_ERROR );
	if (count($result) == 0) { // mime type property not set, return default
		return false;
	}
	return $result[0];
}

/**
 * @return Mandatory arguments to the svn command
 */
function login_getSvnSwitches() {
	$auth = '--username='.escapeArgument(getReposUser()).' --password='.escapeArgument(_getReposPass()).' --no-auth-cache';
	$options = '--non-interactive --config-dir '.escapeArgument(SVN_CONFIG_DIR);
	return $auth.' '.$options;
}

/**
 * Renders error message as XML if SVN command fails. Use trigger_error for normal error message.
 * @TODO output as XHTML tags, so that the output can be used both in XSL and HTML pages
 */
function login_handleSvnError($executedcmd, $errorcode, $output = Array()) {
	echo "<error code=\"$errorcode\">\n";
	if (isset($_GET['DEBUG'])) {
		echo '<exec cmd="'.strtr($executedcmd,'"',"'").'"/>';
	}
	if (is_array($output)) {
		foreach ($output as $row) {
			echo '<output line="'.$row.'"/>';
		}
	}
	echo "</error>\n";
}

/**
 * @return true if the server's PHP installation has the SSL extension
 */
function login_isSSLSupported() {
	return function_exists('openssl_open');
}

// set a cookie to tell javascripts which user this is
// this is set in cleartext under the USERNAME_KEY because it is never used for authentication
function login_setUserSettings() {
	$user = getReposUser();
	if (empty($user)) {
		trigger_error('User not logged in. Can not store username.', E_USER_ERROR);
	}
	setcookie(USERNAME_KEY, $user, 0, '/');
	
	if (!isset($_COOKIE[THEME_KEY])) {
		$style = '';
		// temporarily suggest PE theme for some users
		if ($user=='svensson' || $user=='pe') { 
			$style = 'pe';
		}
		setcookie(THEME_KEY, $style, time()+7*24*3600, '/');	
	}
}

function login_clearUsernameCookie() {
	setcookie(USERNAME_KEY, '', time()-1, '/');
}


?>