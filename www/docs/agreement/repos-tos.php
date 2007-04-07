<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Repos Terms of Service HTML</title>
<link href="../../style/docs.css" rel="stylesheet" type="text/css" />
<style type="text/css">
body, p {
	font-family: monospace;
}
</style>
</head>
<body>
<p>
<?php
/**
 *
 *
 * @package
 */

$f = fopen('repos-tos.txt','r');
$tos = fread($f, 32768);
fclose($f);

echo(nl2br($tos));

?>
</p>
</body>
</html>
