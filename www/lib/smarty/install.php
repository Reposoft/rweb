<?PHP

require(dirname(dirname(dirname(__FILE__))).'/conf/Report.class.php');
require '../uncompress.php';

$report = new Report('Install Smarty');
$report->info("Smarty is installing...");

if (file_exists('libs/')) {
	$report->ok("Smarty libs already installed, done.");
	$report->display();
	exit;
}

$home = "http://smarty.php.net";
$version = "2.6.18";
$archive = "$home/do_download.php?download_file=Smarty-$version.tar.gz";
$repos_package = "Smarty";

$basedir = dirname(__FILE__);
$dir = strtr($basedir, "\\", '/');
$tmp = $dir.'/downloaded.tmp';
$tarfile = "$dir/$repos_package.tar";


if(download($archive, $tmp)) $report->info("Download complete.");
/*
	extract GZ archive
	arg 1 is an absolute path to a gz archive
	arg 2 is the extracted file's name
	arg 3 is optional. default value is 1 000 000 000. it has to be larger than the extracted file 
*/
$report->info("Extract archive...");
if(!uncompressGZ($tmp, $tarfile, 2000000 )) {
	$report->fatal("Not allowed to write to destination $tarfile");
}

/*
	extract TAR archive
	arg 1 is an absolute path to a gz archive
	arg 2 is the extracted file's name. it is optional. default value is the same path as the tar file
	arg 3 is optional. it should be used only if a special directory from the tar file is needed.  
*/
if(uncompressTAR( $tarfile, null, "libs" )) {
	$report->ok("Archive extracted.");
}
System::deleteFile($tmp);
System::deleteFile($tarfile);

$report->ok("Done.");
$report->display();
?> 