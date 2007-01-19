<?php
/**
 * 404 Page Not Found
 */
require('../../../conf/Presentation.class.php');

$p = Presentation::getInstance();

$url = repos_getSelfUrl();

// should be able to view this page without a login
if (!isRepositoryUrl($url)) {
	$p->showErrorNoRedirect(
	'There is no file or folder with the URL '.$url.'.'
	,'Page Not Found');
	exit;
}

require('../../../account/login.inc.php');

if (isRequestInternal()) {
	$p->showErrorNoRedirect('Could not find resource '.$url, '404 Page Not Found');
	exit;
}

// note that we have to do getParent first here, because parent may also be this page
$near = login_getFirstNon404Parent(getParent($url));

$p->showErrorNoRedirect('
There is no file or folder with the URL '.$url.'. It might have been moved or deleted.
<br />For repository resources, find out when it was removed by looking at the history of a parent folder.
</p><p>
The nearest parent folder that exists is <a href="'.$near.'">'.$near.'</a>.'
,'Page Not Found');

?>