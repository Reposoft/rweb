<?php

//header('Content-type: text/plain; charset=utf-8');
header('Content-type: text/html; charset=utf-8');
header("Cache-Control: no-cache, must-revalidate");
echo('<html><body><pre>');

echo("-- Reading 'text' query param --\n");

$var = $_GET['text'];
echo("Value: ".$var."\n");
echo(strlen($var)." characters\n");
echo(mb_strlen($var)." multibyte characters\n");

echo("Detected query param encoding: ".mb_detect_encoding($var)."\n");
// if mbstring.encoding_translation = On is enabled, both the above report same number of characters
echo("PHP input encoding (for GET): ".mb_http_input("G")."\n");
echo("PHP output encoding: ".mb_http_output()."\n");

echo("Value converted to ASCII: ".mb_convert_encoding($var, "ASCII")."\n");
echo("Value converted to Latin 1: ".mb_convert_encoding($var, "ISO-8859-1")."\n");
echo("Value converted to URF-8: ".mb_convert_encoding($var, "UTF-8")."\n");

echo('</pre></body></html>');

?>