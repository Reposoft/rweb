<?php
/**
 *
 *
 * @package admin
 */

require( '../../reposweb.inc.php' );
require( ReposWeb.'conf/Command.class.php' );
require( ReposWeb.'conf/Report.class.php' );

$r = new Report('Storage space used for this repos host');

//$parent = getConfig('home_path');
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
$c->addArg($parent);
$c->exec();

// exit code=1 if there are write protected folders // if ($c->getExitcode()!=0) $r->info($c->getOutput());

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
		// may be /usr/bin/du: `/.../auth': Permission denied
		if (!strContains($line, 'denied')) $r->warn('Unexpected output: '.$line);
		continue;
	}
	$size = $matches[1] . ' ' . $matches[2] . 'b';
	$name = trim(str_replace($parent, '', $matches[3]));
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

if ($r->hasErrors()) $r->error('This listing is incomplete. There were server errors.');

//$r->info($c->getOutput());
$r->display();

?>
