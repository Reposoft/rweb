<?php

require(dirname(dirname(__FILE__)).'/setup.inc.php');

$repo = $test . "repo/";
$admin = $test . "admin/";

if (file_exists($test)) {
	$report->info("Deleting old test repository folder $test");
	deleteFolder($test);
}

$report->info("create test repository");
createFolder($test);
createFolder($repo);
createFolder($admin);

$report->info("Running: svnadmin create \"$repo\"");

setup_svnadmin("create $repo");

$report->info("create user database, base64 or MD5 encoded using htpasswd");
$users =
"svensson:rrE3/9iLvCoFU\n".
"test:n8F28qRYJJ4Q6\n";
$usersencoding = 'base64';
if (isWindows()) { // MD5
	$users = 
	"svensson:\$apr1\$h03.....\$vSQzcy3gId0sKgc/JvRCs.\n".
	"test:\$apr1\$Sy2.....\$zF88UPXW6Q0dG3BRHOQ2m0\n";
	$usersencoding = 'MD5';
}

$userfile = $test . "admin/repos-users";
if (createFileWithContents($userfile, $users, true)) {
	$report->ok("Successfully created user account file $userfile with $usersencoding encoded passwords");
} else {
	$report->fail("Could not create user account file $userfile");
}

$report->info("create ACL");
$aclfile = $test . "admin/repos-access";
$acl = '
[groups]

[/]
* = rw
';
if (createFileWithContents($aclfile, $acl, true)) {
	$report->ok("Successfully created subversion ACL file $aclfile with full access for all users to all folders");
} else {
	$report->fail("Could not create subversion ACL file $aclfile");
}

$report->info("create apache 2.2 config");

$conflocation = '/testrepo';
$conf = "
<Location $conflocation>
DAV svn
SVNIndexXSLT \"/repos/view/repos.xsl\"
SVNPath {$test}repo/
SVNAutoversioning on
# user accounts from password file
AuthName \"$test_repository_folder\"
AuthType Basic
AuthUserFile $userfile
Require valid-user
# standard SVN access control
AuthzSVNAccessFile $aclfile
# don't allow anonymous access, because that would be read-write
Satisfy All
</Location>
";
if (createFileWithContents($conffile, $conf, true)) {
	$report->ok("Successfully created apache config file $conffile");
} else {
	$report->fail("Could not create apache config file $conffile");
}

# check out working copy and create base structure
$wc = $test . "wc/";
createFolder($wc);
$repourl = $repo;

setup_svn("co file:///$repourl $wc");

//system("$svn co file://$repourl $test/wc/");
createFolder($wc."trunk/");

setup_svn("add {$wc}trunk/");

setup_svn('commit -m "Created an empty file archive in repository root" '.$wc);

$report->info('<a href="'.$conflocation.'/trunk/">Log in to trunk</a>');

$report->display();
?>
