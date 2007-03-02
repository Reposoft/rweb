<?php
/**
 * 403 Forbidden
 */
require('../../conf/Presentation.class.php');

$p = Presentation::getInstance();

$url = getSelfUrl();

// user friendly message only needed for remote clients. prevent multiple getFirstNon404Parent
if (isRequestService()) {
	$p->showErrorNoRedirect('Access denied for resouce '.$url,'403 Forbidden');
	exit;
}

// should be able to view this page without a login
if (!isRepositoryUrl($url)) {
	$p->showErrorNoRedirect(
		'Access denied to URL '.$url,
		'403 Forbidden');
	exit;
}

require('../../account/login.inc.php');

// note that we have to do getParent first here, because parent may also be this page
$status = 0;
$near = $url;
while ($status!=200 && ($parent=getParent($near))!==false) {
	$near = login_getFirstNon404Parent($parent, $status);
}

$p->showErrorNoRedirect('
Your user account does not have access rights to URL '.$url.'.
'
.($near==$url ? '' :
'The nearest parent folder that you have access to is <a href="'.$near.'">'.$near.'</a>.'
)
,'Access Denied');

?>