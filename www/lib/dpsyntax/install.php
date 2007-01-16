<HTML>
<HEAD>
</HEAD>
<BODY>
<PRE>
<?PHP
if (file_exists('simpletest/')) {
	echo 'Simpletest is already installed, done.';
	exit;
}

require '../uncompress.php';
// PHP unit testing framework

$repos_package = "dp.SyntaxHighlighter";
$home = "http://www.dreamprojections.com/SyntaxHighlighter/";

$version = "1.4.1";
$archive = "http://www.dreamprojections.com/SyntaxHighlighter/Download.aspx?SyntaxHighlighter_$version.zip";
$license = "http://www.opensource.org/licenses/lgpl-license.php";

$basedir = dirname(__FILE__);
$dir = strtr($basedir, "\\", '/');
$tmp = $dir.'/downloaded.tmp';
$extracted_folder = "$dir/$repos_package/";

if (file_exists($extracted_folder)) {
	echo $repos_package.' is already installed, done.';
	exit;
}

download($archive, $tmp);

decompressZip($tmp, $dir);

System::deleteFile($tmp);
?>
Done.
</PRE>
</BODY>
</HTML>

