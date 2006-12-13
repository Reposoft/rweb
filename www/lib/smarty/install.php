<HTML>
<HEAD>
</HEAD>
<BODY>
<PRE>
<?PHP
if (file_exists('libs/')) {
	echo 'Smarty libs already installed';
	exit;
}

require '../uncompress.php';
$home = "http://smarty.php.net";
$version = "2.6.16";
$archive = "$home/distributions/Smarty-$version.tar.gz";
$file = "Smarty-$version";

$basedir = dirname(__FILE__);
$dir_backslash = rtrim($basedir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
$dir = str_replace('\\', '/', $dir_backslash);

$file = $dir.$file;

/*
	extract GZ archive
	arg 1 is an absolute path to a gz archive
	arg 2 is the extracted file's name
	arg 3 is optional. default value is 1 000 000 000. it has to be larger than the extracted file 
*/
uncompressGZ($archive, $file.".tar", 2000000);

/*
	extract TAR archive
	arg 1 is an absolute path to a gz archive
	arg 2 is the extracted file's name. it is optional. default value is the same path as the tar file
	arg 3 is optional. it should be used only if a special directory from the tar file is needed.  
*/
uncompressTAR($file.".tar", null, "libs");
unlink($file.".tar");  // delete the tar file
?> 
</PRE>
</BODY>
</HTML>