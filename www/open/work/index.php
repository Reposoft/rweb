<?php
require_once( dirname(dirname(dirname(__FILE__))) . "/conf/Presentation.class.php" );
require_once( dirname(dirname(dirname(__FILE__))) . "/account/login.inc.php" );

$target = getTarget();
$downloadUrl = '../../open/cat/?target='.rawurlencode($target).'&rev=HEAD&open';
$repository = getParent(getTargetUrl());

// it is possible to bring up the save as dialog or a client download here, before the new page is shown

$p = new Presentation();
$p->assign('target', $target);
$p->assign('downloadUrl', $downloadUrl);
$p->assign('repository', $repository);

$p->display();

?>