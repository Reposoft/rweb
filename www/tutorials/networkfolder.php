<?php

function upOne($dirname) { return substr($dirname, 0, strrpos(rtrim(strtr($dirname,'\\','/'),'/'),'/') ); }
require( upOne(dirname(__FILE__)) . "/conf/repos.properties.php" );
require( upOne(dirname(__FILE__)) . "/smarty/smarty.inc.php" );

$smarty = getTemplateEngine();

$smarty->assign('url', $_GET['url']);
$smarty->display(dirname(__FILE__).'/networkfolder_sv.html');

?>