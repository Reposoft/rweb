<?php

require(dirname(__FILE__).'/setup.inc.php');

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
demoproject = svensson, test

[/]

[/svensson]
svensson = rw

[/test]
test = rw

[/demoproject]
@demoproject = rw

[/demoproject/trunk/readonly]
@demoproject = r

[/demoproject/trunk/noaccess]
@demoproject = 

[/demoproject/trunk/public]
@demoproject = rw
* = r
';
if (createFileWithContents($aclfile, $acl, true)) {
	$report->ok("Successfully created subversion ACL file $aclfile");
} else {
	$report->fail("Could not create subversion ACL file $aclfile");
}

$report->info("create apache 2.2 config");

$conf = "
DAV svn
SVNIndexXSLT \"/repos/view/repos.xsl\"
SVNPath $test/repo/
SVNAutoversioning on
# user accounts from password file
AuthName \"$test_repository_folder\"
AuthType Basic
AuthUserFile $userfile
Require valid-user
# standard SVN access control
AuthzSVNAccessFile $aclfile
# allow public access to * = r folders
Satisfy Any
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
createFolder($wc."svensson/");
createFolder($wc."svensson/trunk/");
createFolder($wc."svensson/calendar/");
createFolder($wc."test/");
createFolder($wc."test/trunk/");
createFolder($wc."test/calendar/");
createFolder($wc."demoproject/");
createFolder($wc."demoproject/trunk/");
createFolder($wc."demoproject/trunk/noaccess/");
createFolder($wc."demoproject/trunk/readonly/");
createFolder($wc."demoproject/trunk/public/");

$publicxml = $wc."demoproject/trunk/public/xmlfile.xml";
createFileWithContents($publicxml, "<empty-document/>\n");

setup_svn("add {$wc}svensson/");
setup_svn("add {$wc}test/");
setup_svn("add {$wc}demoproject/");
setup_svn("propset svn:mime-type text/xml $publicxml");

setup_svn('commit -m "Created users svensson and test, and a shared project" '.$wc);

# create a base structure in test/trunk/
$folders = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "x", "y", "z");
$testfolder = $wc."test/trunk/";
foreach($folders as $dir){
	$testfolder .= "f$dir/";
	createFolder($testfolder);
	createFileWithContents($testfolder."$dir.txt", "$dir");
}

setup_svn("add {$wc}test/trunk/fa/");
setup_svn('commit -m "Created a sample folder structure for user test" '.$wc);

# other repos projects that need to do integration testing have one folder each below
createFolder($wc."test/trunk/repos-svn-access/");
createFileWithContents($wc."test/trunk/repos-svn-access/automated-test-increment.txt", "0");

setup_svn("add {$wc}test/trunk/repos-svn-access/");
setup_svn('commit -m "Added integration testing folders for other repos projects" '.$wc);

$report->display();
?>
