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
// currently hooks are not added automatically, proably needs a service call
$report->info('<a href="../../../admin/hooks/">Manually create hook scripts so that repos-access can be edited online</a>');

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
if (createFileWithContents($aclfile, $acl, false, true)) {
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

// create administration folder
createFolder($wc."administration/");
// and copy the access file to the administration area for use with hooks
$repositoryacl = $wc.'administration/repos-access';
copy($aclfile, $repositoryacl);

//system("$svn co file://$repourl $test/wc/");
createFolder($wc."svensson/");
createFolder($wc."svensson/trunk/");
//createFolder($wc."svensson/calendar/");
createFolder($wc."test/");
createFolder($wc."test/trunk/");
//createFolder($wc."test/calendar/");
createFolder($wc."demoproject/");
createFolder($wc."demoproject/trunk/");
createFolder($wc."demoproject/trunk/noaccess/");
createFolder($wc."demoproject/trunk/readonly/");
createFileWithContents($wc."demoproject/trunk/readonly/index.html",
	'<html><body>This file should be write protected (folder is "@demoproject = r" in ACL file).</body></html>');

// public contents, allows testing without login
createFolder($wc."demoproject/trunk/public/");
$publicxml = $wc."demoproject/trunk/public/xmlfile.xml";
createFileWithContents($publicxml, "<empty-document/>\n");

// create a sample intranet
createFolder($wc."demoproject/trunk/public/website/");
$publicstyle = $wc."demoproject/trunk/public/website/styles.css";
createFileWithContents($publicstyle, "
body { margin: 15%; color: #223311; }
a { color: #333399; text-decoration: none; }
a:hover { text-decoration: underline; }
");
$publicindex = $wc."demoproject/trunk/public/website/index.html";
createFileWithContents($publicindex, "<html>\n<head>\n<title>demoproject's web</title>
<link href=\"styles.css\" rel=\"stylesheet\" type=\"text/css\" />\n</head>
<body>\n<img src=\"../images/a.jpg\"/><h3>Welcome to our website</h3>\n<p>&nbsp;</p>
<p><small><a href=\"$conflocation/demoproject/trunk/public/\">return to documents</a> &nbsp; | &nbsp; page id: \$Id\$</small></p>\n</html>\n");

setup_svn("add {$wc}*");
setup_svn("propset svn:eol-style native $repositoryacl");
setup_svn("propset svn:mime-type text/xml $publicxml");
setup_svn("propset svn:mime-type text/css $publicstyle");
setup_svn("propset svn:mime-type text/html $publicindex");
setup_svn("propset svn:keywords Id $publicindex");

setup_svn('commit -m "Created users svensson, test and $trickusername, and a shared project" '.$wc);

// Create a locked file
$lockedfile = $wc."demoproject/trunk/public/locked-file.txt";
createFileWithContents($lockedfile, "This file is locked so only one user can change it now.\n");
setup_svn("add $lockedfile");
setup_svn('commit -m "Created a file that will soon be locked by the admin user" '.$wc);
setup_svn('lock -m "Testing lock features. You should not be allowed to modify this file." '.$lockedfile);

// Create a news feed and a calendar in demo project

createFolder($wc."demoproject/messages/");
$newsfile = $wc."demoproject/messages/news.xml";
$contents = new Command('svnlook');
$contents->addArgOption('tree');
$contents->addArg($repo);
$contents->exec();
createFileWithContents($newsfile, '<?xml version="1.0" encoding="utf-8"?>
<?xml-stylesheet type="text/xsl" href="/repos/view/atom.xsl"?>
<feed xmlns="http://www.w3.org/2005/Atom">
	<title>Repos demoproject news</title>
	<modified>'.date('Y-m-d\TH:i:sO').'</modified>
	<id>tag:repos.se,demoproject</id>
	<entry>
		<title>Reset testers\' repository</title>
		<id>tag:repos.se,demoproject,'.microtime().'</id>
		<published>'.date('Y-m-d\TH:i:sO').'</published>
		<author>
			 <name>repos.se testuser</name>
			 <email>test@users.repos.se</email>
		</author>
		<content type="xhtml" xml:lang="en"
		 xml:base="http://www.repos.se/">
		  <div xmlns="http://www.w3.org/1999/xhtml">
		    <p>The test repository has been reset.</p>
		    <pre>'.implode("\n",$contents->getOutput()).'</pre>
		  </div>
		</content>
   </entry>
	<entry>
		<title>Check out the refreshed demo project</title>
		<id>tag:repos.se,demoproject,'.(microtime()+1).'</id>
		<updated>'.date('Y-m-d\TH:i:sO').'</updated>
		<link href="'.repos_getSelfRoot().'/testrepo/demoproject/"/>
		<summary>Repository contents have been reset.</summary>
	</entry>
</feed>
');

createFolder($wc."demoproject/calendar/");
$calendarfile = $wc."demoproject/calendar/demoproject.ics";
$now = date('Ymd\THis\Z');
$later = date('Ymd\THis\Z', time()+3600); 
createFileWithContents($calendarfile,
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
$dir = getTempnamDir();
setup_svn("import -m \"$trickyusername\" $dir \"file:///$repourl$trickyusername\"");
setup_svn("import -m \"\" $dir \"file:///$repourl$trickyusername/trunk\"");
deleteFolder($dir);

// create a base structure in test/trunk/
$folders = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "x", "y", "z");
$testfolder = $wc."test/trunk/";
foreach($folders as $dir){
	$testfolder .= "f$dir/";
	createFolder($testfolder);
	createFileWithContents($testfolder."$dir.txt", "$dir");
}

setup_svn("add {$wc}test/trunk/fa/");
setup_svn('commit -m "Created a sample folder structure for user test" '.$wc);

// other repos projects that need to do integration testing have one folder each below
createFolder($wc."test/trunk/repos-svn-access/");
createFileWithContents($wc."test/trunk/repos-svn-access/automated-test-increment.txt", "0");

setup_svn("add {$wc}test/trunk/repos-svn-access/");
setup_svn('commit -m "Added integration testing folders for other repos projects" '.$wc);

// import big files and folders
$importsFolder = dirname(__FILE__);
setup_svn("import -m \"Created sample images\" $importsFolder/images \"file:///{$repourl}demoproject/trunk/public/images\"");

// clean up
deleteFolder($wc);

// setup done
$report->info('<a href="../restart/">Restart apache to activate new configuration</a>');
$report->info('<a href="'.$conflocation.'/test/trunk/">Directly to repository test account</a>');
$report->info('<a href="/?login">Repos login</a>');

$report->display();
?>
