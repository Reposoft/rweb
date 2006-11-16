<?php
/**
 * Show a nice error message for page not found
 */
require('../../../conf/Presentation.class.php');
require('../../../account/login.inc.php');

$p = new Presentation();

$url = repos_getSelfUrl();

// user friendly message only needed for remote clients. prevent multiple getFirstNon404Parent
if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']=='127.0.0.1'
		&& !isset($_SERVER['HTTP_USER_AGENT'])) { // only do this short message for internal getHeaders function
	$p->showErrorNoRedirect('URL not found '.$url);
	exit;
}

// note that we have to do getParent first here, because parent may also be this page
$near = login_getFirstNon404Parent(getParent($url));

$p->showErrorNoRedirect('
<p>There is no file or folder with the URL '.$url.'. It might have been moved or deleted.
<br />For repository resources, check the history of a parent folder
to locate the removed file or folder in older revisions.</p>
<p>The nearest parent folder that exists is <a href="'.$near.'">'.$near.'</a>.</p>
');

?>