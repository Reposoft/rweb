<?php
/**
 * Runs svnadmin verify for the repository
 *
 * @package
 */
 
require('../repos-backup.inc.php' );

require( ReposWeb.'/conf/Report.class.php' );
$report = new Report();

$repo = getConfig('local_path');

$command = new Command('svnadmin');
$command->addArgOption('verify', $repo);
// it is unlikely that this page is called form command line, so we might as well output html
echo('<pre>');
$command->passthru();
echo('</pre>');

$report->display();

?>
