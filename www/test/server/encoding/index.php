<?php

//mb_internal_encoding("UTF-8");

//header('Content-type: text/plain; charset=utf-8');
header('Content-type: text/html; charset=utf-8');
header("Cache-Control: no-cache, must-revalidate");
echo('<html><body><pre>');
echo('<a href="?text=n%c3%a4d%c3%a5">test UTF-8: &quot;n&auml;d&aring;&quot;</a>'."\n");
echo('<a href="?text=n%E4d%E5">test Latin-1: &quot;n&#xE4;d&#xE5;&quot;</a>'."\n");
echo("<a href=\"?text=n\xc3\xa4d\xc3\xa5\">test non-encoded UTF-8 url: &quot;n\xc3\xa4d\xc3\xa5&quot;</a>\n");

echo("-- Reading 'text' query param --\n");

$var = $_GET['text'];
echo("Value: ".$var."\n");

echo(strlen($var)." characters\n");
echo(mb_strlen($var)." multibyte characters\n");

echo("Detected query param encoding: ".mb_detect_encoding($var)."\n");
// if mbstring.encoding_translation = On is enabled, both the above report same number of characters
echo("PHP internal encoding: ".mb_internal_encoding()."\n");
echo("PHP input encoding (for GET): ".mb_http_input("G")."\n");
echo("PHP output encoding: ".mb_http_output()."\n");

echo("Value converted to ASCII: ".mb_convert_encoding($var, "ASCII")."\n");
echo("Value converted to Latin 1: ".mb_convert_encoding($var, "ISO-8859-1")."\n");
echo("Value converted to URF-8: ".mb_convert_encoding($var, "UTF-8")."\n");

echo("-- use command line --\n");
require('../../../conf/repos.properties.php');
$tmp = tempnam(str_replace('/','\\',rtrim(getTempDir(),'/')), 'cmdtest');
echo("run 'echo $var > $tmp', then passthru file\n");
passthru('echo'. " $var > $tmp 2>&1");

$fp = fopen($tmp, 'r');
$filecontents = fread($fp, 1024);
fclose($fp);
deleteFile(toPath($tmp));
echo("Value: ");
echo($filecontents);
echo("Detected encoding of contents read from file: ".mb_detect_encoding($filecontents)."\n");

echo('</pre></body></html>');

