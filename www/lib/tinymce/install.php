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

$repos_package = "tinymce";
$home = "http://tinymce.moxiecode.com/";

$version = "2_0_9";
$archive = "http://downloads.sourceforge.net/tinymce/tinymce_$version.zip?download";
$license = "http://wiki.moxiecode.com/index.php/TinyMCE:License";

$basedir = dirname(__FILE__);
$dir = strtr($basedir, "\\", '/');
$tmp = $dir.'/downloaded.tmp';
$extracted_folder = "$dir/$repos_package/";

if (file_exists($extracted_folder)) {
	echo $repos_package.' is already installed, done.';
	exit;
}

if(download($archive, $tmp)) echo("Download complete.\n");

decompressZip($tmp, $dir);

System::deleteFile($tmp);
?>
Done.
</PRE>
</BODY>
</HTML>

