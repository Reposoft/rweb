<?PHP
require(dirname(dirname(dirname(__FILE__))).'/conf/Report.class.php');
require '../uncompress.php';
$report = new Report('Install ImageMagick');

require('convert.inc.php');
$convert = convertGetCommand();
$return = exec("$convert --version", $output);
// there is a windows command called "convert" but this seems to work anyway
if ($return == 0 && $output) {
	$report->ok("$output[0] is installed, done.");
	$report->display();
	exit;
}
if (!System::isWindows()) {
	$report->error('This is not a windows system. ImageMagick should be installed in path using the package manager.');
	$report->display();
	exit;
}

$repos_package = "ImageMagick";
$home = "http://www.imagemagick.org/";

$version = "6.3.2";
$versiondl = "$version-3-Q16";
$archive = "http://www.imagemagick.org/download/binaries/ImageMagick-$versiondl-windows-dll.exe";
$license = "http://www.imagemagick.org/script/license.php";
$report->info("ImageMagick license: $license");

$basedir = dirname(__FILE__);
$dir = strtr($basedir, "\\", '/');
//$tmp = $dir.'/downloaded.tmp';
//$extracted_folder = "$dir/$repos_package-$version/";
$install_folder = "$dir/installed/"; // see convert.inc.php
$install_folder = strtr($install_folder, '/', '\\');

$report->info("Manual installation required.");
$report->info("Download ImageMagick from <a href=\"$archive\">$archive</a>");
$report->info("and install to <code>$install_folder</code> (or in system PATH)");
$report->display();
?>