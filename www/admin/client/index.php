<?php

require('../../conf/Report.class.php');
require('../../account/login.inc.php');

$report = new Report("Repos SVN client configuration");

$r = getRepository();
$rurl = parse_url($r);
$name = $rurl['host'];

if(isset($_GET['ca'])) {
	importCertificateAuthority($report, $_GET['ca'], $name);
}
if(isset($_GET[SUBMIT]) && $_GET[SUBMIT]='accept') {
	acceptCertificate($report, $r);
}

//$report->info('http://svnbook.red-bean.com/nightly/en/svn.advanced.html#svn.advanced.confarea');

$config = SVN_CONFIG_DIR . 'config';
if (file_exists($config)) {
	$report->ok("Found client runtime config file $config");
} else {
	$report->fail("Could not locate client runtime config file $config");
}

$configuration = parse_ini_file($config, true);

foreach ($configuration as $g => $c) {
	$report->info("[$g]");
	foreach ($c as $k => $v) {
		if (empty($v)) $v = "(false)";
		if (strBegins($k, '#')) {
			$report->debug("$k = $v");
		} else {
			$report->info("$k = $v");
		}
	}
}

// manage groups
if (!isset($configuration['groups'])) {
	$report->warn("There are no configured server groups");
} else {
	$groups = $configuration['groups'];
	$report->ok("Found [groups] section with ".count($groups)." entries.");
}

if (strBegins($r, 'https://')) {
	$report->info("$r is an SSL repository, checking certificate");
	$test = login_svnRun("info ".$r);
	$report->debug($test);
	if ($e = array_pop($test)) {
		if ($e == 1 && strContains($test[1], 'not trusted')) {
			handleCertificateNotTrusted($report);
		} else {
			// login invalid, but we don't need login
			$report->ok("Repository access is OK. No furthere certificate management needed.");	
		}
	} else {
		$report->ok("Repository access is OK. No furthere certificate management needed.");
	}
}

$report->display();

function handleCertificateNotTrusted($report) {
	$report->fail("The server certificate of this repository is not signed by a trusted issuer");
	$report->info("The Certificate Authority must be added to the runtime config. Paste a URL to the CA.crt here:");
	//$report->info('<form method="get" action="#"><input name="ca" type="text" size="60"/><input type="submit"/></form>');
	$report->info('<form method="get" action="#"><input name="submit" value="accept" type="submit"/></form>');
}

/**
 * Runs an svn command, accepting the certificate permanently.
 *
 * @param unknown_type $repository
 */
function acceptCertificate($report, $repository) {
	// a command without no-auth-cache that is used to probe the certificate
	$cmd = getCommand('svn').' --username=test --password=test --config-dir '.SVN_CONFIG_DIR.' info '.$repository;
	
	$report->info("Run the following command on the server, and chose to accept certificate permanently:");
	$report->info(array($cmd));
	
	// Create a pseudo terminal for the child process
	/*
	$descriptorspec = array(
       0 => array("pipe", "r"),  // stdin
       1 => array("pipe", "w"),  // stdout
       2 => array("pipe", "w")  // stderr
	);
	$process = proc_open($cmd, $descriptorspec, $pipes);
	if (is_resource($process)) {
	   $line = fread($pipes[1], 512);
	   $report->debug($line);
	   $error = fgets($pipes[2], 8192);
	   if (!empty($error)) $report->error($error);
	   fwrite($pipes[0], 'p');
	   
	   proc_close($process);
	} else {
		$report->fail("Could not open command line process $cmd to accept certificate");
	}
	$report->ok("Permanently accepted certificate for repository $repository");
	*/
}

/**
 * Add certificate authority to runtime configuration area.
 * This is based on the ant script in svn-config-dir,
 * but has not been verified. Use acceptCertificate instead.
 *
 * @param unknown_type $report
 * @param unknown_type $caUrl
 * @param unknown_type $name
 */
function importCertificateAuthority($report, $caUrl, $name) {
	if (!strBegins($caUrl, 'http://') && !strBegins($caUrl, 'https://')) {
		$report->error("CA $caUrl is not a valid HTTP URL.");
		return;
	}
	$s = getHttpStatus($caUrl);
	if ($s == 200) {
		$report->ok("Located CA at URL: $caUrl");
	} else {
		$report->error("Could not locate CA at URL: $caUrl. HTTP status $s");
		return;
	}
	
	$certdir = SVN_CONFIG_DIR . 'accepted-ssl' . DIRECTORY_SEPARATOR;
	$cert = toPath($certdir.$name.'.crt');
	if (file_exists($cert)) {
		$report->error("The certificate file $cert already exists. Remove it manually and try again.");
		return;
	}
	if (downloadFile($caUrl, $cert)) {
		$report->ok("Downloaded CA $caUrl to $cert");
	} else {
		$report->error("Error downloading CA $caUrl to $cert");
	}
	
	addServerConfig($report, SVN_CONFIG_DIR.'config', $name, $cert);
}

function addServerConfig($report, $file, $name, $certificateFile) {
	if (!file_exists($file)) trigger_error("The runtime config file $file does not exist");
	$f = fopen($file, 'a');
	if (!$f) trigger_error("Could not open config file $file for writing");
	fwrite($f, "\n");
	fwrite($f, "[groups]\n"); // assuming there is no groups section yet
	fwrite($f, "$name = $name\n");
	fwrite($f, "\n");
	fwrite($f, "[$name]\n");
	fwrite($f, "http-timeout = ".URL_FOPEN_TIMEOUT."\n"); // same as the url-fopen timeout in login.inc.php
	fwrite($f, "ssl-authority-files = ".str_replace('/', DIRECTORY_SEPARATOR, $certificateFile)."\n");
	fclose($f);
}

function downloadFile($url, $localPath) {
	$from = fopen($url, 'rb');
	if (!$from) trigger_error("Could not open CA url $url");
	$contents = '';
	while (!feof($from)) {
	  $contents .= fread($from, 8192);
	}
	return createFileWithContents($localPath, $contents);
}

?>