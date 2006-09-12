<?php
header('Content-type: text/xml; charset=UTF-8');

setcookie('repos_testcookie', 'ok');

$xmltext = "Sadly, it seems your browser does not support XML transformation.";

echo('<?xml version="1.0" encoding="UTF-8"?>'."\n");
echo('<?xml-stylesheet type="text/xsl" href="browsercheck.xsl"?>'."\n");
echo("<testpage>\n");
echo("$xmltext\n");
echo("</testpage>\n");
