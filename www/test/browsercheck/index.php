<?php
header('Content-type: text/xml; charset=UTF-8');

// there is a nice extension for firefox to view cookies in page info: https://addons.mozilla.org/firefox/315/
setcookie('repos_testcookie', 'ok', time()+3600, "/");

$xmltext = "Sadly, it seems your browser does not support XML transformation.";

echo('<?xml version="1.0" encoding="UTF-8"?>'."\n");
echo('<?xml-stylesheet type="text/xsl" href="browsercheck.xsl"?>'."\n");
echo("<testpage>\n");
echo("$xmltext\n");
echo("</testpage>\n");
