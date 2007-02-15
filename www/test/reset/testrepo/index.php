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

// the working copy where the initial state is created
$wc = setup_getTempWorkingCopy();
$trickyusername = 'Sv@n s-on'; // duplicate of that in createTestUsers

setup_deleteCurrent();

$report->info("Running: svnadmin create \"$repo\"");
setup_svnadmin("create $repo");

setup_createHooks();

// demouser (svensson), test and tricky work together in demoproject
// administrator does not have a home folder
setup_createTestUsers();
$acl = "
[groups]
administrators = admin
demoproject = svensson, test, $trickyusername, admin

[/]
@administrators = rw

[/administration]
@administrators = rw

[/svensson]
svensson = rw

[/test]
test = rw

[/$trickyusername]
$trickyusername = rw

[/demoproject]
@demoproject = rw

[/demoproject/trunk/readonly]
@demoproject = r

[/demoproject/trunk/noaccess]
@demoproject = 

[/demoproject/trunk/public]
@demoproject = rw
* = r
";
if (System::createFileWithContents($aclfile, $acl, false, true)) {
	$report->ok("Successfully created subversion ACL file $aclfile");
} else {
	$report->fail("Could not create subversion ACL file $aclfile");
}

setup_createApacheLocation(
"# standard SVN access control
AuthzSVNAccessFile $aclfile
# allow public access to * = r folders
Satisfy Any",
"# disable caching for directory listing, because ETag seems not 100% compatible with firefox
<Location ~ \"^$conflocation/.*/$\">
	Header add Cache-Control \"no-cache\"
</Location>"
);

// check out working copy and create base structure
$repourl = $repo;
setup_svn("co file:///$repourl $wc");

// add all the testrepo files
setup_svn("export --force \"".dirname(__FILE__)."/contents/\" $wc");
$repositoryacl = $wc.'administration/repos-access.acl';
$publicxml = $wc."demoproject/trunk/public/xmlfile.xml";
$publicindex = $wc."demoproject/trunk/public/website/index.html";
$publicstyle = $wc."demoproject/trunk/public/website/styles.css";

// user accounts, same folder layout as in access/create/
define('REPOSITORY_USER_FILE_NAME', 'repos-password.htp');
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

setup_svn("add {$wc}*");
setup_svn("propset svn:eol-style native $repositoryacl");
setup_svn("propset svn:mime-type text/xml $publicxml");
setup_svn("propset svn:mime-type text/css $publicstyle");
setup_svn("propset svn:mime-type text/html $publicindex");
setup_svn("propset svn:keywords Id $publicindex");

setup_svn('commit -m "Created users svensson, test and $trickusername, and a shared project" '.$wc);

$lockedfile = $wc."demoproject/trunk/public/locked-file.txt";
//setup_svn("add $lockedfile");
//setup_svn('commit -m "Created a file that will soon be locked by the admin user" '.$wc);
setup_svn('lock -m "Testing lock features. You should not be allowed to modify this file." '.$lockedfile);

// Create a news feed and a calendar in demo project

//System::createFolder($wc."demoproject/messages/");
$newsfile = $wc."demoproject/messages/news.xml";
$contents = new Command('svnlook');
$contents->addArgOption('tree');
$contents->addArg($repo);
$contents->exec();
if (file_exists($newsfile)) System::deleteFile($newsfile); 
System::createFileWithContents($newsfile, '<?xml version="1.0" encoding="utf-8"?>
<?xml-stylesheet type="text/xsl" href="/repos/view/atom.xsl"?>
<feed xmlns="http://www.w3.org/2005/Atom">
	<title>Repos demoproject news</title>
	<modified>'.date('Y-m-d\TH:i:sO').'</modified>
	<id>tag:repos.se,demoproject</id>
	<entry>
		<title>Testers\' repository was reset</title>
		<id>tag:repos.se,demoproject,'.microtime().'</id>
		<published>'.date('Y-m-d\TH:i:sO').'</published>
		<author>
			 <name>repos.se testuser</name>
			 <email>test@users.repos.se</email>
		</author>
		<content type="xhtml" xml:lang="en"
		 xml:base="http://www.repos.se/">
		  <div xmlns="http://www.w3.org/1999/xhtml">
		    <p>The test repository has been reset. Check '.getSelfRoot().'/testrepo/demoproject/</p>
		    <pre>'.implode("\n",$contents->getOutput()).'</pre>
		  </div>
		</content>
   </entry>
</feed>
');
// don't use a <link> to the repository because then the client requests login when reading the feed

//System::createFolder($wc."demoproject/calendar/");
$calendarfile = $wc."demoproject/calendar/demoproject.ics";
$now = date('Ymd\THis\Z');
$later = date('Ymd\THis\Z', time()+3600);
if (file_exists($calendarfile)) System::deleteFile($calendarfile); 
System::createFileWithContents($calendarfile,
"BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//repos.se//NONSGML repos//EN
BEGIN:VEVENT
DTSTART:$now
DTEND:$later
SUMMARY:Try the repos repository
END:VEVENT
BEGIN:VTODO
DTSTAMP:$now
SEQUENCE:2
UID:uid:repos.se-123456789@
ORGANIZER:MAILTO:test@users.repos.se
ATTENDEE;PARTSTAT=ACCEPTED:MAILTO:svensson@users.repos.se
DUE:$later
STATUS:NEEDS-ACTION
SUMMARY:Complete this sample ToDo
END:VTODO
END:VCALENDAR
");

setup_svn("add {$wc}demoproject/messages/");
setup_svn("propset svn:mime-type text/xml $newsfile");
setup_svn("add {$wc}demoproject/calendar/");
setup_svn('commit -m "Created demo news and demo calendar" '.$wc);

// PHP mkdir can not handle UTF-8 characters
$dir = System::getTempFolder();
setup_svn("import -m \"$trickyusername\" $dir \"file:///$repourl$trickyusername\"");
setup_svn("import -m \"\" $dir \"file:///$repourl$trickyusername/trunk\"");
System::deleteFolder($dir);

// create a base structure in test/trunk/
$folders = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "x", "y", "z");
$testfolder = $wc."test/trunk/";
foreach($folders as $dir){
	$testfolder .= "f$dir/";
	System::createFolder($testfolder);
	System::createFileWithContents($testfolder."$dir.txt", "$dir");
}

setup_svn("add {$wc}test/trunk/fa/");
setup_svn('commit -m "Created a sample folder structure for user test" '.$wc);

// other repos projects that need to do integration testing have one folder each below
System::createFolder($wc."test/trunk/repos-svn-access/");
System::createFileWithContents($wc."test/trunk/repos-svn-access/automated-test-increment.txt", "0");

setup_svn("add {$wc}test/trunk/repos-svn-access/");
setup_svn('commit -m "Added integration testing folders for other repos projects" '.$wc);

// import big files and folders
$importsFolder = dirname(__FILE__);
setup_svn("import -m \"Created sample images\" $importsFolder/images \"file:///{$repourl}demoproject/trunk/public/images\"");

// clean up
System::deleteFolder($wc);

// setup done
$report->info('<a href="../restart/">Restart apache to activate new configuration</a>');
$report->info('<a href="'.$conflocation.'/test/trunk/">Directly to repository test account</a>');
$report->info('<a href="/?login">Repos login</a>');

$report->display();
?>
