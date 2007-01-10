<?php
/**
 *
 *
 * @package admin
 */
require('../repos-backup.inc.php');

$repodir = getConfig('local_path');
if ( !is_dir($repodir)) {
	fatal("The configured repository folder '$repodir' does not exist.");
}
if ( isRepository($repodir) ) {
	fatal("There is already a repository in folder '$repodir'");
}
if (count(getDirContents($repodir))) fatal("Folder '$repodir' is not empty."); 
 
$command = new Command('svnadmin');
$command->addArgOption('create');
$command->addArg($repodir);
$command->exec();
if ($command->getExitcode()) {
	fatal($command->getOutput());
} else {
	info("Successfully created empty repository in $repodir");
}

html_end();

?>
