<?php
/**
 * 403 Forbidden
 */
require('../../../conf/Presentation.class.php');
require('../../../account/login.inc.php');

$p = Presentation::getInstance();

$url = repos_getSelfUrl();

// user friendly message only needed for remote clients. prevent multiple getFirstNon404Parent
if (isRequestInternal()) {
	$p->showErrorNoRedirect('Access denied for resouce '.$url,'403 Forbidden');
	exit;
}

// note that we have to do getParent first here, because parent may also be this page
$status = 0;
$near = $url;
while ($status!=200 && ($parent=getParent($near))!==false) {
	$near = login_getFirstNon404Parent($parent, $status);
}

$p->showErrorNoRedirect('
Your user account does not have access rights to URL '.$url.'.
</p><p>'
.($near==$url ? '' :
'The nearest parent folder that you have access to is <a href="'.$near.'">'.$near.'</a>.'
)
,'Access Denied');

?>