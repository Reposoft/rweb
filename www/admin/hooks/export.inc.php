<?php
/**
 * Export functinality for hook scripts
 *
 * @package admin
 */

// user account password file
define('PASSWORD_FILE', 'administration/repos-password.htp');
// repository ACL
define('ACL_FILE', 'administration/repos-access.acl');

require('../../open/ServiceRequest.class.php');

function exportUsers($rev, $repo, $changes) {
	// TODO fix so that this is the last operaton, or redirect user to login page after commit
	$pattern = '/^([^\/]+)\/'.preg_quote(PASSWORD_FILE, '/').'$/';
	foreach ($changes as $path => $change) {
		if ($change != 'D' && preg_match($pattern, $path, $matches)) {
			$user = $matches[1];
			_exportUserPassword($user);
		}
	}
}

function _exportUserPassword($username) {
	$url = getWebapp().'admin/accountrevert/';
	$r = new ServiceRequest($url, array('username'=>$username), false);
	$r->exec();
	if ($r->isOK()) {
		echo("Successfully exported password for user $username\n");
	} else {
		echo("Error for user $username: ".$r->getResponse()."\n");
	}
}

function exportAdministration($rev, $repo, $changes) {
	if (!getConfig('admin_folder')) trigger_error("Admin folder not set", E_USER_ERROR);
	if (!getConfig('access_file')) trigger_error("Access file not set", E_USER_ERROR);
	$pattern = '/^'.preg_quote(ACL_FILE, '/').'$/';
	foreach ($changes as $path => $change) {
		if ($change != 'D' && preg_match($pattern, $path, $matches)) {
			$destination = getConfig('admin_folder').getConfig('access_file');
			_exportFile($repo, $path, $rev, $destination);
		}
	}	
}

/**
 * do the exports specified by config file
 *
 * @param unknown_type $rev
 * @param unknown_type $repo
 * @param unknown_type $changes
 */
function exportOptional($rev, $repo, $changes) {
	if (!getConfig('exports_file')) trigger_error('No exports defined. Set "exports_file" in repos.properties.');
	$exports_file = getConfig('admin_folder').getConfig('exports_file');
	
	$exports = parse_ini_file($exports_file);
	$patterns = array();
	foreach ($exports as $src => $target) {
		if (isAbsolute($target)) {
			trigger_error('For security reasons, export targets must start with recognized keywords.', E_USER_ERROR);
		} else {
			$p = strpos($target, '/');
			$folder = substr($target, 0, $p);
			$target = getRealPath($folder) . substr($target, $p+1);
		}
		// changes are without leading slash
		$p = '/^'.preg_quote(ltrim($src,'/'),'/').'(.*)$/';
		$patterns[$p] = $target;
	}

	foreach ($changes as $path => $change) {
		if ($change == 'D') continue;
		foreach ($patterns as $p => $target) {
			if (preg_match($p, $path, $matches)) {
				$destination = $target.$matches[1];
				if (isFolder($path)) {
					if ($change == 'A' && !file_exists($destination)) System::createFolder($destination);
				} else {
					_exportFile($repo, '/'.$matches[0], $rev, $destination);
				}
			}
		}
	}
}

/**
 * Derive the local path from folder keywords.
 *
 * @param String $hostFolder keyword folder without trailing slash
 * @return String absolute local path if keyword is recognized
 */
function getRealPath($hostFolder) {
	$known_folders = array(
		'admin' => getConfig('admin_folder'),
		'html' => toPath(dirname(dirname(dirname(dirname(__FILE__)))).'/'),
		'backup' => getConfig('backup_folder'),
		'repo' => getConfig('local_path')
	);
	if (!array_key_exists($hostFolder, $known_folders)) {
		trigger_error("Export target '$target' is not recognized.", E_USER_ERROR);
		exit; // for the sake of security
	}
	return $known_folders[$hostFolder];
}

/**
 * Write file from repository to local file.
 */
function _exportFile($repo, $path, $revision, $destination) {
	$c = new Command('svnlook');
	$c->addArgOption('cat');
	$c->addArgOption('-r '.$revision);
	$c->addArg($repo);
	$c->addArg($path);
	$c->exec();
	$out = $c->getOutput();
	if ($c->getExitcode()) {
		trigger_error("Could not read committed file $path revision $revision: ".implode("\n",$out), E_USER_ERROR);
	} else {
		$handle = fopen($destination, 'w');
		for ($i = 0; $i < count($out); $i++) {
			$p = $out[$i].System::getNewline();
			fwrite($handle, $p);
		}
		fclose($handle);
		echo ("Exported $path to $destination\n");
	}
}
 

?>
