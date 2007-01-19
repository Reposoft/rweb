<?php
/**
 *
 *
 * @package
 */
require('../../conf/Presentation.class.php');
require('../SvnEdit.class.php');

$targeturl = getTargetUrl();

$template = Presentation::getInstance();

$unlock = new SvnEdit('unlock');
$unlock->addArgUrl($targeturl);
$unlock->exec();
displayEdit($template, getParent($targeturl));

?>
