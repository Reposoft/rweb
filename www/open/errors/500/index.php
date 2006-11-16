<?php
/**
 * Show a nice error message for page not found
 */
require('../../../conf/Presentation.class.php');
require('../../../account/login.inc.php');

$p = new Presentation();

$url = repos_getSelfUrl();

$p->showError('
HTTP 500 error.
This is a server error that could not be handled automatically, at the URL '.$url.'.
<br />This type of error is often temporary. If it happens repeatedly, 
contact <a href="mailto:support@repos.se">support@repos.se</a>.
');
?>