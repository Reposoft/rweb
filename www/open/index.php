<?php
define('PARENT_DIR',substr(dirname(__FILE__), 0, strrpos(rtrim(strtr(dirname(__FILE__),'\\','/'),'/'),'/')));
require( PARENT_DIR."/login.inc.php" );

// get file to open

// #debug#
$path = $_GET['path'];
$file = $_GET['file'];
// #######

$url = getTargetUrl();
$type = substr($url, strrpos($url, '.') + 1);

// iCalendar files
if ($type=='ics') {
	/* PHP iCalendsr config must be changed to: 
		$allow_webcals  = 'yes';
		$allow_login = 'yes';
	*/
	header("Location: ../phpicalendar/?cal=$url");
} else {
?>
<html>
<head>
<title>Open file in online editor</title>
</head>
<body>
<p>Support for file type &quot;<?php echo $type; ?>&quot; not added yet.</p>
<p>Click the file name to download and open the file</p>
<p><a href="#" onclick="history.back()">Back</a></p>
</body>
<?php
}
?>