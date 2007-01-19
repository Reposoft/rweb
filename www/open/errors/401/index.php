<?php
/**
 * 401 Unauthorized
 */
require('../../../conf/Presentation.class.php');

$p = Presentation::getInstance();

$p->showErrorNoRedirect(
'This URL requires login.
If you provided a login, this error means that username or password is invalid, so access is refused.
<br />Use the logout button to clear any saved credentials from your browser, then try login again.
'
,'Not Authenticated');

?>