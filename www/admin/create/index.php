<?php
/**
 *
 *
 * @package admin
 */
require('../repos-backup.inc.php');

require(dirname(dirname(dirname(__FILE__))) . "/conf/Report.class.php" );
$report = new Report();

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

$report->info('Return to <a id="admin" href="../">admin</a> or <a if="load" href="../load/">load backup</a>.');

html_end();

?>
