<?php
require(dirname(dirname(__FILE__)).'/conf/repos.properties.php');

// dispatcher
if ($_SERVER['REQUEST_METHOD']=='POST') {
	storeLogEntry();
} else if (isset($_GET['clear'])) {
	clear();
} else if (count($_GET)>0) {
	trigger_error("Reports should be POSTed");
} else {
	showLog();
}

function getLogfile() {
	return getTempDir('errorlog').'errorlog.txt';
}

function clear() {
	$logfile = getLogfile();
	$fp = fopen($logfile, 'w+');
	if ($fp) {
		fclose($fp);
	} else {
		trigger_error("Could not clear the file $logfile");
	}
	header("Location: ?");
}

function storeLogEntry() {
	$logfile = getLogfile();
	$entry = implode_with_key($_POST)."\n";
	$fp = fopen($logfile, 'a');
	if ($fp) {
		fwrite($fp, date('Y-m-dTH:m:sP').' ');
		fwrite($fp, $entry);
		fclose($fp);
	} else {
		trigger_error("Could not write to file $logfile");
	}
	header("Location: ?");
}

function implode_with_key($assoc, $inglue = '=', $outglue = '&')
{
   $return = null;
   foreach ($assoc as $tk => $tv) $return .= $outglue.$tk.$inglue.$tv;
   return substr($return,1);
}

function showLog() {
	$logfile = getLogfile();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="/repos/style/global.css" rel="stylesheet" type="text/css" />
<title>Repos errorlog</title>
</head>

<body onload="location.href='#end'">
<pre>
<?php
	$fp = fopen($logfile, 'r');
	if ($fp) {
		fpassthru($fp);
		fclose($fp);
	} else {
		trigger_error("Could not read logfile $logfile");
	}
?>
</pre>
<p><a class="action" href="./">refresh</a></p>
<hr />
<form action="./" method="post">
<p><label for="message">Error message</label></p>
<p><textarea name="message" id="textarea" cols="60"></textarea></p>
<p>
    <label for="Submit">Submit:</label>
    <input type="submit" id="Submit" />
</p>
</form>
<p align="right"><a name="end"></a><small><a class="action" href="?clear">Clear the error log</a></small></p>
</body>
</html>
<?php
}
?>
