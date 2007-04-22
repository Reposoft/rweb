<?PHP
require(dirname(dirname(dirname(__FILE__))).'/conf/Report.class.php');
require '../uncompress.php';

$report = new Report('Install dp.SyntaxHighlighter');

if (file_exists('dp.SyntaxHighlighter/')) {
	$report->ok("dp.SyntaxHighlighter is already installed, done.");
	$report->display();
	exit;
}


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


if(download($archive, $tmp)) {
	$report->info("Download complete.");
} else {
	$report->fatal("Download failed.");
}

decompressZip($tmp, $dir);

System::deleteFile($tmp);

$report->ok("Done.");
$report->display();
?>