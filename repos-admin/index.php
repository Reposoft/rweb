<?php

require('reposweb.inc.php');
require(ReposWeb.'conf/Presentation.class.php');

$p = Presentation::getInstance();
$p->assign('repository', getRepository());
$p->display();

?>