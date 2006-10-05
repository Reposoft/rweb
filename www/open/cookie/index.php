<?php
// return a cookie value in ajax response for browsers like firefox 1.5 that don't have cookies in xhtml+xml documents
$name=$_GET['name'];
if (isset($_COOKIE[$name])) {
	echo $_COOKIE[$name];
}
?>