<HTML>
<HEAD>
</HEAD>
<BODY>
<PRE>
<?PHP
require '../uncompress.php';
// PHP unit testing framework


$repos_package = "simpletest";
$home = "simpletest.sourceforge.net";

$version = "1.0.1alpha3";
$archive = "http://heanet.dl.sourceforge.net/sourceforge/simpletest/simpletest_$version.tar.gz";

$basedir = dirname(__FILE__);
$dir_backslash = rtrim($basedir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$dir = str_replace('\\', '/', $dir_backslash);
$extracted_folder = "$dir/simpletest-$version";

/*
	extract GZ archive
	arg 1 is an absolute path to a gz archive
	arg 2 is the extracted file's name
	arg 3 is optional. default value is 1 000 000 000. it has to be larger than the extracted file 
*/
uncompressGZ($archive, $extracted_folder.".tar", 10000000 );

$filename = $extracted_folder.".tar";

/*
	extract TAR archive
	arg 1 is an absolute path to a gz archive
	arg 2 is the extracted file's name. it is optional. default value is the same path as the tar file
	arg 3 is optional. it should be used only if a special directory from the tar file is needed.  
*/
uncompressTAR( $filename, null, null );

unlink($filename);  // delete the tar file

?>
</PRE>
</BODY>
</HTML>