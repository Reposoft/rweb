<HTML>
<HEAD>
</HEAD>
<BODY>
<PRE>
<?PHP
if (file_exists('simpletest/')) {
	echo 'Simpletest is already installed';
	exit;
}

require '../uncompress.php';
// PHP unit testing framework

$repos_package = "simpletest";
$home = "simpletest.sourceforge.net";

$version = "1.0.1beta";
$archive = "http://switch.dl.sourceforge.net/sourceforge/simpletest/simpletest_$version.tar.gz";

$basedir = dirname(__FILE__);
$dir_backslash = rtrim($basedir, DIRECTORY_SEPARATOR);
$dir = str_replace('\\', '/', $dir_backslash);
$extracted_folder = "$dir/simpletest-$version";

/*
	extract GZ archive
	arg 1 is an absolute path to a gz archive
	arg 2 is the extracted file's name
	arg 3 is optional. default value is 1 000 000 000. it has to be larger than the extracted file 
*/
uncompressGZ($archive, $extracted_folder.".tar", 2000000 );

$filename = $extracted_folder.".tar";

/*
	extract TAR archive
	arg 1 is an absolute path to a gz archive
	arg 2 is the extracted file's name. it is optional. default value is the same path as the tar file
	arg 3 is optional. it should be used only if a special directory from the tar file is needed.  
*/
uncompressTAR( $filename, null, null );

unlink($filename);  // delete the tar file

// delete the docs and test folder
System::deleteFolder($dir.'/simpletest/docs/');
System::deleteFolder($dir.'/simpletest/test/');

// As long as we want to be compatible with PHP 4, exceptions are syntax errors. remove the code from simpletest.
$exceptionsfile = $dir.'/simpletest/exceptions.php';
if (!file_exists($exceptionsfile)) trigger_error("Could not locate $exceptionsfile, download must have failed.");
$fh = fopen($exceptionsfile, 'w');
fwrite($fh, "<?php /* removed by repos because it was not PHP4 compatible */ ?>");
fclose($fh);

?>
</PRE>
</BODY>
</HTML>

