<?php
// TODO make link of "history of parent folder"

/**
 * 404 Page Not Found
 */
define('REPOS_SERVICE_NAME', 'errors/404/');
require('../../conf/Presentation.class.php');

$p = Presentation::getInstance();

$url = getSelfUrl();

if (isRequestService()) {
	$p->showErrorNoRedirect('Could not find resource '.$url, '404 Page Not Found');
	exit;
}

// should be able to view this page without a login
if (!isRepositoryUrl($url)) {
	$p->showErrorNoRedirect(
	'There is no file or folder with the URL '.$url.'.'
	,'Page Not Found');
	exit;
}

require('../../account/login.inc.php');

// note that we have to do getParent first here, because parent may also be this page
$near = login_getFirstNon404Parent(getParent($url), $status);

$p->showErrorNoRedirect('
There is no file or folder with the URL '.$url.'. It might have been moved or deleted.
<br />For repository resources, find out when it was removed by looking at the history of a parent folder.
</p><p>
The nearest parent folder that exists is <a href="'.$near.'">'.$near.'</a>.'
,'Page Not Found');

?>
