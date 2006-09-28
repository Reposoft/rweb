<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>repos.se: Contents of {=$target|basename}</title>
<!--{$head}-->
</head>

<body>
<pre>
<?php

//  name the temp dir where the repository will be. This dir will be removed recursively.
$tst="test.repos.se";
$here=getcwd();

//echo "Restoring the repos.se test repository to its baseline"
//echo ""

# environment setup

//exec("svn");

//export LANG="en_US.UTF-8"
//export LC_ALL="en_US.UTF-8"
// svn command alias

//The PATH to SVN and SVNADMIN has to be defined in the SYSTEMPATH, not in the USERPATH
$svn="svn --config-dir " . rtrim($here, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "test-svn-config-dir";


// Get temporary directory
if (!empty($_ENV['TMP'])) {
		$tempdir = $_ENV['TMP'];
} elseif (!empty($_ENV['TMPDIR'])) {
		$tempdir = $_ENV['TMPDIR'];
} elseif (!empty($_ENV['TEMP'])) {
		$tempdir = $_ENV['TEMP'];
} else {
		$tempdir = dirname(tempnam('', 'na'));
}

if (empty($tempdir)) { die ('No temporary directory'); }

$test = rtrim($tempdir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $tst;
$repo = rtrim($test, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "repo";
$admin = rtrim($test, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "admin";
$conf = $test . "/admin/testrepo.conf";
$conf_apachefriendly = str_replace('\\', '/', $conf);
echo "\n\n\n";
echo "Apache should do \"Include $conf_apachefriendly\" at some &lt;Location &gt;\n";
echo "Note that apache must be restarted if there are changes in this file.\n";
echo "\n\n\n";

if (file_exists($repo)) {
	removeDirectory($test);
} else {
	if (file_exists($test)) {
		echo "Is $test really the right test dir? It already exists but does not contain the testrepository";
	}
}

# create test repository

mkdir($test, 0777);
mkdir($repo, 0777);
mkdir($admin, 0777);

#svnadmin create "$test/repo/"

chdir($repo);
$createRepos="svnadmin create " . $repo . " 2>&1";
$result0 = array();
exec($createRepos, &$result0);
foreach ( $result0 as $v0 ) {
	echo "$v0 \n";
}
//system($createRepos);
chdir($here);

# create user database, base64 encoded by htpasswd2
$users = $test . "/admin/repos-users";
//touch($users);
$userfile = fopen($users, 'ab');
fwrite($userfile, "svensson:\$apr1\$h03.....\$vSQzcy3gId0sKgc/JvRCs.\n");
fwrite($userfile, "test:\$apr1\$Sy2.....\$zF88UPXW6Q0dG3BRHOQ2m0");
fclose($userfile);

# create ACL
$acl = $test . "/admin/repos-access";
//touch($acl);
$accessfile = fopen($acl, 'ab');
fwrite($accessfile, "[groups]\n");
fwrite($accessfile, "demoproject = svensson, test\n");
fwrite($accessfile, "\n");
fwrite($accessfile, "[/]\n");
fwrite($accessfile, "\n");
fwrite($accessfile, "[/svensson]\n");
fwrite($accessfile, "svensson = rw\n");
fwrite($accessfile, "\n");
fwrite($accessfile, "[/test]\n");
fwrite($accessfile, "test = rw\n");
fwrite($accessfile, "\n");
fwrite($accessfile, "[/demoproject]\n");
fwrite($accessfile, "@demoproject = rw\n");
fwrite($accessfile, "\n");
fwrite($accessfile, "[/demoproject/trunk/readonly]\n");
fwrite($accessfile, "@demoproject = r\n");
fwrite($accessfile, "\n");
fwrite($accessfile, "[/demoproject/trunk/noaccess]\n");
fwrite($accessfile, "@demoproject = \n");
fwrite($accessfile, "\n");
fclose($accessfile);

# create apache 2.2 config

//touch($conf);
$conffile = fopen($conf, 'ab');
fwrite($conffile, "DAV svn\n");
fwrite($conffile, "SVNIndexXSLT \"/repos/view/repos.xsl\"\n");
fwrite($conffile, "SVNPath $test/repo/\n");
fwrite($conffile, "SVNAutoversioning on\n");
fwrite($conffile, "AuthName \"$tst\"\n");
fwrite($conffile, "AuthType Basic\n");
fwrite($conffile, "AuthUserFile $users\n");
fwrite($conffile, "Require valid-user\n");
fwrite($conffile, "AuthzSVNAccessFile $acl\n");
fclose($conffile);

//echo "Apache should do \"Include $CONF\" at some <Location >"
//echo "Note that apache must be restarted if there are changes in this file."
//echo ""

# check out working copy and create base structure

mkdir($test . DIRECTORY_SEPARATOR . "wc", 0777);
$repourl = str_replace('\\', '/', $repo);
$result_CO = array();
exec("$svn co file:///$repourl $test/wc/ 2>&1", &$result_CO);
foreach ( $result_CO as $v_CO ) {
	echo "$v_CO \n";
}
//system("$svn co file://$repourl $test/wc/");
mkdir($test . "/wc/svensson", 0777);
mkdir($test . "/wc/svensson/trunk", 0777);
mkdir($test . "/wc/svensson/calendar", 0777);
mkdir($test . "/wc/test", 0777);
mkdir($test . "/wc/test/trunk", 0777);
mkdir($test . DIRECTORY_SEPARATOR . "wc" . DIRECTORY_SEPARATOR . "test" . DIRECTORY_SEPARATOR . "calendar", 0777);
mkdir($test . DIRECTORY_SEPARATOR . "wc" . DIRECTORY_SEPARATOR . "demoproject", 0777);
mkdir($test . DIRECTORY_SEPARATOR . "wc" . DIRECTORY_SEPARATOR . "demoproject" . DIRECTORY_SEPARATOR . "trunk", 0777);
mkdir($test . DIRECTORY_SEPARATOR . "wc" . DIRECTORY_SEPARATOR . "demoproject" . DIRECTORY_SEPARATOR . "trunk" . DIRECTORY_SEPARATOR . "noaccess", 0777);
mkdir($test . DIRECTORY_SEPARATOR . "wc" . DIRECTORY_SEPARATOR . "demoproject" . DIRECTORY_SEPARATOR . "trunk" . DIRECTORY_SEPARATOR . "readonly", 0777);
$result1 = array();
exec("$svn add $test/wc/svensson 2>&1", &$result1);
foreach ( $result1 as $v1 ) {
	echo "$v1 \n";
}
$result2 = array();
exec("$svn add $test/wc/test 2>&1", &$result2);
foreach ( $result2 as $v2 ) {
	echo "$v2 \n";
}
$result3 = array();
exec("$svn add $test/wc/demoproject 2>&1", &$result3);
foreach ( $result3 as $v3 ) {
	echo "$v3 \n";
}
$result4 = array();
exec("$svn commit -m \"Created users svensson and test, and a shared project\" $test/wc/ 2>&1", &$result4);
foreach ( $result4 as $v4 ) {
	echo "$v4 \n";
}
//system("$svn add $test/wc/svensson");
//system("$svn add $test/wc/test");
//system("$svn add $test/wc/demoproject");
//system("$svn commit -m \"Created users svensson and test, and a shared project\" $test/wc/");

# create a base structure
$folders = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "x", "y", "z");
$testfolder = "$test/wc/test/trunk";
foreach($folders as $dir){
	$testfolder="$testfolder/f$dir";
	mkdir($testfolder, 0777);
	$folder = fopen("$testfolder/$dir.txt", 'ab');
	fwrite($folder, "$dir");
	fclose($folder);
}

$result5 = array();
exec("$svn add $test/wc/test/trunk/fa 2>&1", &$result5);
foreach ( $result5 as $v5 ) {
	echo "$v5 \n";
}
$result6 = array();
exec("$svn commit -m \"Created a sample folder structure for user test\" $test/wc/ 2>&1", &$result6);
foreach ( $result6 as $v6 ) {
	echo "$v6 \n";
}
//system("$svn add $test/wc/test/trunk/fa");
//system("$svn commit -m \"Created a sample folder structure for user test\" $test/wc/");

# other repos projects that need to do integration testing have a folder each below
mkdir("$test/wc/test/trunk/repos-svn-access", 0777);

$autotest = "$test/wc/test/trunk/repos-svn-access/automated-test-increment.txt";
$autotestinc = fopen($autotest, 'ab');
fwrite($autotestinc, "0");
fclose($autotestinc);


$result7 = array();
exec("$svn add $test/wc/test/trunk/repos-svn-access 2>&1", &$result7);
foreach ( $result7 as $v7 ) {
	echo "$v7 \n";
}
$result8 = array();
exec("$svn commit -m \"Added integration testing folders for other repos projects\" $test/wc/ 2>&1", &$result8);
foreach ( $result8 as $v8 ) {
	echo "$v8 \n";
}
//system("$svn add $test/wc/test/trunk/repos-svn-access");
//system("$svn commit -m \"Added integration testing folders for other repos projects\" $test/wc/");

function removeDirectory($dir) {
  if ($handle = opendir($dir)) {
   while (false !== ($item = readdir($handle))) {
     if ($item != "." && $item != "..") {
       if (is_dir($dir . DIRECTORY_SEPARATOR . $item)) {
	    $directory = $dir . DIRECTORY_SEPARATOR . $item;
		chmod($directory, 0777);
        removeDirectory($directory);
       } else {
	    $file = $dir . DIRECTORY_SEPARATOR . $item;
	    chmod($file, 0777);
        unlink($file);
       }
     }
   }
   closedir($handle);
   chmod($dir, 0777);
   rmdir($dir);
  }
}

?>
</pre>
</body>
</html> 