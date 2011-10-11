<?php
/**
 * 403 Forbidden
 */
define('REPOS_SERVICE_NAME', 'errors/403/');
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

// find the nearest allowed parent folder
require('../../account/login.inc.php');

// note that we have to do getParent first here, because parent may also be this page
$status = 0;
$near = $url;
while ($status!=200 && ($parent=getParent($near))!==false) {
	$near = login_getFirstNon404Parent($parent, $status);
}

// if nearest accessible folder is server root (parent of repository root), user is leaving a project - show startpage
$outside = getParent(getRepository());
$userWantsStartpage = ($near == $outside) && isLoggedIn()
	&& isset($_COOKIE[USERNAME_KEY]) // make sure client is browser-like and has used the ?login function
	&& strContains(getHttpReferer(), substr($url, 5)); // only if coming from subfolder, ignore http/https
if ($userWantsStartpage) {
	$startpage = asLink(getWebapp().'open/start/?denied='.rawurlencode($url));
	header('Location: '.$startpage);
}

// if user is authorized to one of the parent folders in the repository, show a link
$p->showErrorNoRedirect('
Your user account does not have access rights to URL '.$url.'.
'
.($near==$url ? '' :
' The nearest parent folder that you have access to is <a href="'.$near.'">'.$near.'</a>.'
)
,'Access Denied');

?>