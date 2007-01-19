<?php
/**
 * Represents a subversion read operation which does not need a working copy.
 * 
 * TODO this is a work in progress. functions are being converted from login.inc.php
 */

// use test mocks if they define global function targetLogin()
if (!function_exists('targetLogin')) require(dirname(dirname(__FILE__)).'/account/login.inc.php');

if (!class_exists('Command')) require(dirname(dirname(__FILE__)).'/conf/Command.class.php');
if (!class_exists('Rule')) require(dirname(dirname(__FILE__)).'/plugins/validation/validation.inc.php');

define('HEAD','HEAD');

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
	$result = _command_run('svn', $svnCommand);
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
 * @return Mandatory arguments to the svn command, safe for command line (config dir path is escaped)
 * @deprecated should be done only by the SvnOpen command
 */
function login_getSvnSwitches() {
	$auth = '--username='.escapeArgument(getReposUser()).' --password='.escapeArgument(_getReposPass()).' --no-auth-cache';
	$options = '--non-interactive --config-dir '.Command::_escapeArgument(SVN_CONFIG_DIR);
	return $auth.' '.$options;
}

/**
 * Renders error message as XML if SVN command fails. Use trigger_error for normal error message.
 * @TODO output as XHTML tags, so that the output can be used both in XSL and HTML pages
 * @deprecated should be handled by SvnOpen command
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
 * @return revision number from parameters (safe as command argument), false if not set
 * @deprecated use RevisionRule instead
 */
function getRevision($rev = false) {
	if (!$rev) {
		if(!isset($_GET['rev'])) {
			return false;
		}
		$rev = $_GET['rev'];
	}
	if (is_numeric($rev)) {
		return $rev;
	}
	$accepted = array('HEAD');
	if (in_array($rev, $accepted)) {
		return $rev;
	}
	trigger_error("Error. Revision number '$rev' is not valid.", E_USER_ERROR);
}

/**
 * Validates revision (number or string) parameters.
 */
class RevisionRule extends Rule {
	
	function RevisionRule($fieldname='rev', $message='Not a valid revision number') {	
		$this->Rule($fieldname, $message);
	}
	
	function valid($value) {
		if (!$value) return true; // Use Validation::expect to require a valud
		if (is_numeric($value) && $value >=0 ) return true;
		if ($value == HEAD) return true;
		if (strpos($value,'{')==0 && strrpos($value, '}')==strlen($value)-1) return true;
		// no other keywords than HEAD accepted
		return false;
	}
	
}

/**
 * Runs an SVN command that results in text output (repository information).
 * 
 * Content type can be either text/plain or text/xml.
 * 
 * @see Command for the basic functionality
 * @see SvnOpenFile for reading file contents
 */
class SvnOpen {
	
	/**
	 * The command instance that we delegate to (preferred over subclassing).
	 * @var Command
	 */
	var $command;
	
	// store the svn operation for reference
	var $operation;
	
	/**
	 * Creates the command representation.
	 *
	 * @param String $subversionOperation like list or info
	 * @param boolean $asXml set to true to add the --xml parameter (allowed only if the svn command accepts it)
	 * @return SvnOpen
	 */
	function SvnOpen($subversionOperation, $asXml=false) {
		$this->operation = $subversionOperation;
		$this->command = new Command('svn');
		$this->_addSvnOptions();
		$this->command->addArgOption($subversionOperation);
		if ($asXml) $this->command->addArgOption('--xml');
	}
	
	/**
	 * All svn requests are made as the current authenticated user.
	 * @return String username
	 * @static 
	 */
	function getAuthenticatedUser() {
		return getReposUser();
	}
	
	function _addSvnOptions() {
		$this->command->addArgOption(login_getSvnSwitches());
	}
	
	/**
	 * @param String $url full http or https URL, not urlencoded
	 */
	function addArgUrl($url) {
		$url = urlEncodeNames($url); // allow UTF-8 characters in url on windows too (or do we get problems with this)
		$this->command->addArg($url);	
	}
	
	/**
	 * Uniquely identifies an object in subversion, for a url (history) that may have contained different objects.
	 * See http://svnbook.red-bean.com/nightly/en/svn-book.html#svn.advanced.pegrevs
	 * @param String $url the URL, just like addArgUrl
	 * @param int|String $revision, the revision number to append as URL@PEG-REV
	 */
	function addArgUrlPeg($url, $revision) {
		$url = urlEncodeNames($url).'@'.$revision;
		$this->command->addArg($url);
	}
	
	/**
	 * @param String $path filename or valid local path
	 */
	function addArgPath($path) {
		$this->command->addArg($path);
	}
	
	function addArgRevision($revision) {
		$this->command->addArgOption('-r', $revision);
	}
	
	function addArgRevisionRange($revisionRange) {
		$this->addArgRevision($revisionRange);
	}
	
	function addArgOption($option, $value=null, $valueNeedsEscape=true) {
		$this->command->addArgOption($option, $value, $valueNeedsEscape);
	}
	
	/**
	 * @return String the svn operation for the command
	 */
	function getOperation() {
		return $this->operation;
	}
	
	/**
	 * The arguments should be handled with care, because they reveal system internals.
	 * Also this function reconstructs the arguments, repeating the logic from the
	 * exec call, and then sanitized with regexps, so it is not efficient.
	 * @return String the custom arguments to the svn operation
	 */
	function _getArgumentsString() {
		$arg = $this->command->_getArgumentsString();
		return trim(preg_replace(array(
			'/--username[=\s]+"?[^"]*"?\s+/',
			'/--password[=\s]+"?[^"]*"?\s+/',
			'/--no-auth-cache\s+/',
			'/--non-interactive\s+/',
			'/--config-dir[=\s]+"?[^"]*"?\s+/',
			'/'.$this->getOperation().'\s+/'
		), array('','','','','',''), $arg, 1));
		// --username="" --password="" --no-auth-cache --non-interactive --config-dir "C:\\srv\\ReposServer\\admin\\svn-config-dir\\
	}
	
	/**
	 * Runs the svn command
	 * @return int the exit code
	 */
	function exec() {
		return $this->command->exec();
	}
	
	/**
	 * Passes the command output directly to browser without buffering,
	 * and also without error handling.
	 * This method should only be used for administration tasks. Useful when output is large.
	 * @return int the exit code, generally 0 if successful
	 */
	function passthru() {
		return $this->command->passthru();
	}
	
	function getExitcode() {
		return $this->command->getExitcode();
	}
	
	function getOutput() {
		return $this->command->getOutput();
	}
	
	function getContentLength() {
		return $this->command->getContentLength();
	}
	
}

$info2 = '<?xml version="1.0"?>
<info>
<entry
   kind="file"
   path="php-templates.xml"
   revision="17">
<url>http://localhost/testrepo/demoproject/trunk/php-templates.xml</url>
<repository>
<root>http://localhost/testrepo</root>
<uuid>8f625040-5a68-5746-9b6d-4d3e05d10a73</uuid>
</repository>
<commit
   revision="9">
<author>test</author>
<date>2006-12-13T17:28:19.057156Z</date>
</commit>

<lock>
<token>opaquelocktoken:935a41cc-526f-1d41-b1ae-f1e3f9e9afb7</token>
<owner>test</owner>
<comment>solsson (which is the system username from office application)</comment>
<created>2006-12-14T15:36:43.193200Z</created>
<expires>2006-12-14T15:39:25.193200Z</expires>
</lock>
</entry>
</info>';

$list2 = '<?xml version="1.0"?>
<lists>
<list
   path="http://localhost/testrepo/demoproject/trunk/php-templates.xml">
<entry
   kind="file">
<name>php-templates.xml</name>
<size>291</size>
<commit
   revision="20">
<author>svensson</author>
<date>2006-12-14T15:48:32.553209Z</date>
</commit>
</entry>
</list>
</lists>';

define('SVN_KIND_FOLDER', 1);
define('SVN_KIND_FILE', 2);

/**
 * Models a verbose svn info call.
 * To get info for a folder: do "svn info --xml path"
 * To get info for a file (including size): to "svn list --xml path)"
 */
class SvnInfo extends SvnOpen {
	var $kind;
	var $name;
	// the first revision number is the latest in that part of the repository, it is usually not helpful
	var $url;
	var $repository;
	var $revision;
	var $author;
	var $date;
	var $lock;
	var $size;
	
	function SvnInfo($path) {
		
	}
	
	function _parse($xmlArray) {
		
	}
	
	
}

class SvnLock {
	var $token;
	var $owner;
	var $comment;
	var $created;
	var $expires;
}

?>