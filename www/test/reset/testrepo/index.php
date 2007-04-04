<?php
/**
 * Creates a test repository with a known initial state, that all the integration tests can use.
 * 
 * This script should be able to set up all the testing possibilities for the full repos funcitonality,
 * which means that it will be big. It is allowed to add contents at any time,
 * but not to change existing setu, because tests might depend on it.
 * 
 * Run the complete intergation test suite before and after modifications in this script.
 * 
 * @package test
 */

require(dirname(dirname(__FILE__)).'/setup.inc.php');

// Working copy where the initial repository state is created
$wc = setup_getTempWorkingCopy();

$report->info("Delete and create repository \"$repo\"...");
setup_deleteCurrent();
setup_svnadmin("create $repo");
setup_createTestUsers();
setup_createHooks();

// Custom apache configuration for this repository
setup_createApacheLocation(
"# standard SVN access control
AuthzSVNAccessFile $aclfile
# allow public access to * = r folders
Satisfy Any
",
''
);

// Check out working copy and create base structure
$repourl = $repo;
setup_svn("co file:///$repourl $wc");

// User accounts, same folder layout as in access/create/
define('REPOSITORY_USER_FILE_NAME', 'repos.user');
System::createFolder($wc."svensson/");
System::createFolder($wc."svensson/trunk/");
System::createFolder($wc."svensson/administration/");
System::createFileWithContents($wc."svensson/administration/".REPOSITORY_USER_FILE_NAME,
	'svensson:$apr1$h03.....$vSQzcy3gId0sKgc/JvRCs.:Testuser Svensson:test@repos.se'."\n"
);

System::createFolder($wc."test/");
System::createFolder($wc."test/trunk/");
System::createFolder($wc."test/administration/");
System::createFileWithContents($wc."test/administration/".REPOSITORY_USER_FILE_NAME,
	'test:$apr1$Sy2.....$zF88UPXW6Q0dG3BRHOQ2m0:Testuser Test:test@repos.se'."\n"
);

$exportContents = dirname(__FILE__).'/contents/';
if (!file_exists("$exportContents.svn")) { //setup_svn("info \"$exportContents\"")) {
	setup_copyContents($exportContents, $wc);
} else {
	setup_svn("export --force \"$exportContents\" $wc");
}

setup_svn("add {$wc}*");

// demo repository should not contain unsupported tools
if (strContains(getRepository(), 'demo')) {
	setup_svn("revert {$wc}demoproject/branches/");
	setup_svn("revert {$wc}demoproject/tags/");
	setup_svn("revert {$wc}demoproject/tasks/");
	setup_svn("revert {$wc}demoproject/messages/");
	setup_svn("revert {$wc}demoproject/calendar/");
	setup_svn("revert {$wc}demoproject/templates/");
}

$repositoryacl = $wc.'administration/repos.accs';
$publicxml = $wc."demoproject/trunk/public/xmlfile.xml";
$publicindex = $wc."demoproject/trunk/public/website/index.html";
$publicstyle = $wc."demoproject/trunk/public/website/styles.css";

setup_svn("propset svn:eol-style native $repositoryacl");
setup_svn("propset svn:mime-type text/xml $publicxml");
setup_svn("propset svn:mime-type text/css $publicstyle");
setup_svn("propset svn:mime-type text/html $publicindex");
setup_svn("propset svn:keywords Id $publicindex");

setup_svn('commit --username admin -m "Imported testrepo contents. Created default test users svensson and test." '.$wc);

// Lock a file as the SYSTEM user
$lockedfile = $wc."demoproject/trunk/public/locked-file.txt";
setup_svn('lock --username svensson -m "Testing lock features. You should not be allowed to modify this file." '.$lockedfile);

// Update a document so we get a diff
$htmldocument = $wc."demoproject/trunk/Policy document.html";
setup_svn("propset svn:mime-type text/html \"$htmldocument\"");
setup_replaceInFile($htmldocument, array(
	'</body>' => "<p>Feel free to update this document.</p>\n</body>"
));

// Delete a document so we can test that
setup_svn("rm \"{$wc}demoproject/trunk/public/temp.txt\"");

// Delete a folder that has a document in it
setup_svn("rm \"{$wc}demoproject/trunk/old/\"");

setup_svn('commit --username test -m "Added a policy for the policy document. Deleted an old file." '.$wc);

// Create a news feed and a calendar in demo project
$newsfile = $wc."demoproject/messages/news.xml";
setup_replaceInFile($newsfile, array(
	'{=$date}' => date('Y-m-d\TH:i:sO'),
	'{=$repository}' => getSelfRoot().'/testrepo'
));
$calendarfile = $wc."demoproject/calendar/demoproject.ics";
setup_replaceInFile($calendarfile, array(
	'{=$now}' => date('Ymd\THis\Z'),
	'{=$later}' => date('Ymd\THis\Z', time()+3600)
));
setup_svn("propset svn:mime-type text/xml $newsfile");
setup_svn('commit --username admin -m "Created demo news and demo calendar" '.$wc);

// Create a base structure in test/trunk/
$folders = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "x", "y", "z");
$testfolder = $wc."test/trunk/";
foreach($folders as $dir){
	$testfolder .= "f$dir/";
	System::createFolder($testfolder);
	System::createFileWithContents($testfolder."$dir.txt", "$dir");
}
setup_svn("add {$wc}test/trunk/fa/");
System::createFolder($wc."test/trunk/trunk/");
System::createFolder($wc."test/trunk/trunk/trunk/");
setup_svn("add {$wc}test/trunk/trunk/");
setup_svn('commit -m "Created a abnormal folder structure for user test" '.$wc);

// Other repos projects that need to do integration testing have one folder each below
System::createFolder($wc."test/trunk/repos-svn-access/");
System::createFileWithContents($wc."test/trunk/repos-svn-access/automated-test-increment.txt", "0");
setup_svn("add {$wc}test/trunk/repos-svn-access/");
setup_svn('commit -m "Added integration testing folders for other repos projects" '.$wc);

// Clean up
System::deleteFolder($wc);

// Setup done
$report->info('<a href="../restart/">Restart apache to activate new configuration</a>');
$report->info('<a href="'.$conflocation.'/test/trunk/">Directly to repository test account</a>');
$report->info('<a href="/?login">Repos login</a>');

$report->display();
?>
