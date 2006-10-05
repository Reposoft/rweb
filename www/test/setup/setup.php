<?php

require('../../conf/Report.class.php');
$report = new Report('set up test repository');

//  name the temp dir where the repository will be. This dir will be removed recursively.
$test_repository_folder="test.repos.se/";

//echo "Restoring the repos.se test repository to its baseline"
//echo ""

# environment setup
$here=getcwd();
$svnargs="--config-dir " . rtrim($here, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "test-svn-config-dir";

// On windows, the PATH to SVN and SVNADMIN has to be defined in the SYSTEMPATH, not in the USERPATH

// Get temporary directory
$tempdir = getSystemTempDir();

$test = $tempdir . $test_repository_folder;
$repo = $test . "repo/";
$admin = $test . "admin/";

if (file_exists($test)) {
	$report->info("Deleting old test repository folder $test");
	deleteFolder($test);
}

# create test repository
createFolder($test);
createFolder($repo);
createFolder($admin);

$report->info("Running: svnadmin create \"$repo\"");

$result = repos_runCommand('svnadmin', "create " . escapeArgument($repo));
if (array_pop($result)) {
	$report->fail("Could not create repository: ".implode("\n", $result));
} else {
	$report->ok("Successfully created repository $repo");
}

# create user database, base64 encoded by htpasswd2
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

# create ACL
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

# create apache 2.2 config
$conffile = $test . "admin/testrepo.conf";

$conf = "
DAV svn
SVNIndexXSLT \"/repos/view/repos.xsl\"
SVNPath $test/repo/
SVNAutoversioning on
AuthName \"$test_repository_folder\"
AuthType Basic
AuthUserFile $users
Require valid-user
AuthzSVNAccessFile $acl
Satisfy Any   # allow public access to * = r folders
";
if (createFileWithContents($conffile, $conf, true)) {
	$report->ok("Successfully created subversion ACL file $aclfile");
} else {
	$report->fail("Could not create subversion ACL file $aclfile");
}

$report->info("Apache should do \"Include $conffile\" at some &lt;Location &gt;");
$report->info("Note that apache must be restarted when there is changes in the conf file.");

# check out working copy and create base structure
$wc = $test . "wc/";
createFolder($wc);
$repourl = $repo;

$result = repos_runCommand('svn', $svnargs.' co '.escapeArgument("file:///$repourl").' '.$wc);
if (array_pop($result) || count($result)==0) {
	$report->fail("Error executing the checkout command");
} else {
	$report->ok("Successfully checked out $repourl to working copy $wc");
	$report->debug($result);
}

//system("$svn co file://$repourl $test/wc/");
createFolder($wc."svensson/");
createFolder($wc."svensson/trunk/");
createFolder($wc."svensson/calendar/");
createFolder($wc."test/");
createFolder($wc."test/trunk/");
createFolder($wc."test/calendar/");
createFolder($wc."demoproject/");
createFolder($wc."demoproject/trunk/");
createFolder($wc."demoproject/noaccess/");
createFolder($wc."demoproject/readonly/");
createFolder($wc."demoproject/public/");

$publicxml = $wc."demoproject/public/xmlfile.xml";
createFileWithContents($publicxml, "<empty-document/>\n");

$report->debug(repos_runCommand('svn', $svnargs.' add '.escapeArgument($wc."svensson")));
$report->debug(repos_runCommand('svn', $svnargs.' add '.escapeArgument($wc."test")));
$report->debug(repos_runCommand('svn', $svnargs.' add '.escapeArgument($wc."demoproject")));
$report->debug(repos_runCommand('svn', $svnargs.' propset svn:mime-type text/xml '.escapeArgument($publicxml)));

$result = repos_runCommand('svn', $svnargs.' commit -m "Created users svensson and test, and a shared project" '.escapeArgument($wc));
if (array_pop($result) || count($result)==0) {
	$report->fail("Could not commit test folders using svn command");
} else {
	$report->ok("Successfully committed test folders");
	$report->debug($result);
}

# create a base structure in test/trunk/
$folders = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "x", "y", "z");
$testfolder = $wc."test/trunk/";
foreach($folders as $dir){
	$testfolder .= "f$dir/";
	createFolder($testfolder);
	createFileWithContents($testfolder."$dir.txt", "$dir");
}

$report->debug(repos_runCommand('svn', $svnargs.' add '.escapeArgument($wc.'test/trunk/fa/')));
$result = repos_runCommand('svn', $svnargs.' commit -m "Created a sample folder structure for user test" '.escapeArgument($wc));
if (array_pop($result) || count($result)==0) {
	$report->fail("Could not commit folder tree using svn command");
} else {
	$report->ok("Successfully committed folder tree");
	$report->debug($result);
}

# other repos projects that need to do integration testing have one folder each below
createFolder($wc."test/trunk/repos-svn-access/");
createFileWithContents($wc."test/trunk/repos-svn-access/automated-test-increment.txt", "0");

$report->debug(repos_runCommand('svn', $svnargs.' add '.escapeArgument($wc.'test/trunk/repos-svn-access/')));
$result = repos_runCommand('svn', $svnargs.' commit -m "Added integration testing folders for other repos projects" '.escapeArgument($wc));
if (array_pop($result) || count($result)==0) {
	$report->fail("Could not commit integration test folders using svn command");
} else {
	$report->ok("Successfully committed integration test folders");
	$report->debug($result);
}

$report->display();
?>
