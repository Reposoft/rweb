<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="../../style/global.css" rel="stylesheet" type="text/css"></link>
<title>accounts menu</title>
<style type="text/css">
#hostid {
	padding: 5px;
	border: 1px dashed gray;
}
</style>
</head>

<body>

<div id="commandbar">
<a id="reposadmin" href="../">admin</a>
</div>

<h1>Server Host ID</h1>
<h2><code id="hostid">
<?php
if (function_exists('zend_get_id')) {
	$id = zend_get_id();
	echo $id[0];
} else {
	echo 'Error: Zend Optimizer is not installed';
}
?>
</code></h2>
<div id="footer">
</div>
</body>
</html>
