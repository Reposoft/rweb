<?php
/**
 *
 *
 * @package admin
 */

require(dirname(dirname(dirname(__FILE__))).'/conf/Command.class.php');
require(dirname(dirname(dirname(__FILE__))).'/conf/Report.class.php');

$r = new Report('Storage space used for this repos host');

//$home = getConfig('home_path');
$parent = getParent(getConfig('admin_folder'));
if ($parent != getParent(getConfig('local_path'))
	|| $parent != getParent(getConfig('backup_folder')))
	$r->fatal('This is not a standard repos host. Admin, local and backup should have same parent folder.');

if (System::isWindows()) {
	$r->fatal('This funtionality is not available on Windows servers');
}
	
$c = new Command('du');
$c->addArgOption('-hc');
$c->addArgOption('--max-depth=1');
$c->addArg($home);
$c->exec();

if ($c->getExitcode()!=0) {
	$r->info($c->getOutput());
	$r->fatal('Unable to check folder size');
}

//$r->info($c->getOutput());

$expected = array(
	'admin' => 'Administration folder',
	'repo' => 'The repository',
	'backup' => 'Backup folder',
	'html' => 'Web contents',
	'total' => 'Total size'
);

$pattern = '/^([\d\.]+)(\w)\s+(\S.*)/';
foreach($c->getOutput() as $line) {
	preg_match($pattern, $line, $matches);
	if (count($matches) < 4) {
		$r->warn('Unexpected output: '.$line);
		continue;
	}
	$size = $matches[1] . ' ' . $matches[2] . 'b';
	$name = trim(str_replace($home, '', $matches[3]));
	if (strlen($name) < 2) continue;
	if ($name == '.svn') continue;
	if (array_key_exists($name, $expected)) {
		$name = $expected[$name];
	}
	if ($matches[3] == 'total') {
		$r->ok($name.': '.$size);
	} else {
		$r->info($name.': '.$size);
	}
}

//$r->info($c->getOutput());
$r->display();

?>
