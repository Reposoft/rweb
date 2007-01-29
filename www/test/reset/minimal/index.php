<?php
/**
 * Creates a nearly empty repository, with the bare minimum needed to run repos.
 * 
 * @package test
 */

require(dirname(dirname(__FILE__)).'/setup.inc.php');

$wc = setup_getTempWorkingCopy();

setup_deleteCurrent();

$report->info("Running: svnadmin create \"$repo\"");

setup_svnadmin("create $repo");

setup_createTestUsers();

$report->info("create ACL");
$acl = '
[groups]

[/]
* = rw
';
if (System::createFileWithContents($aclfile, $acl, true, true)) {
	$report->ok("Successfully created subversion ACL file $aclfile with full access for all users to all folders");
} else {
	$report->fail("Could not create subversion ACL file $aclfile");
}

setup_createApacheLocation("
# standard SVN access control
AuthzSVNAccessFile $aclfile
# don't allow anonymous access, because that would be read-write
Satisfy All
");

# check out working copy and create base structure
$repourl = $repo;

setup_svn("co file:///$repourl $wc");

//system("$svn co file://$repourl $test/wc/");
System::createFolder($wc."trunk/");

setup_svn("add {$wc}trunk/");

setup_svn('commit -m "Created an empty file archive in repository root" '.$wc);

$report->info('Please note that Apace needs to be restarted. This repostory uses Satisfy All instead of Satisfy Any.');

// clean up
System::deleteFolder($wc);

setup_reloadApacheIfPossible();

$report->info('<a href="'.$conflocation.'/trunk/">Log in to trunk</a>');
// end report
$report->display();
?>
