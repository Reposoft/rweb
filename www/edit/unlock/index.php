<?php
/**
 *
 *
 * @package
 */
require('../../conf/Presentation.class.php');
require('../SvnEdit.class.php');

$targeturl = getTargetUrl();

$template = new Presentation();

$unlock = new SvnEdit('unlock');
$unlock->addArgUrl($targeturl);
$unlock->exec();
$unlock->present($template, getParent($targeturl));

?>
