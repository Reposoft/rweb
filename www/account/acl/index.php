<?php
/**
 * ideas for keeping the ACL version controlled
 */

// admin account identification (used for example to add extra post-commit operation in upload)
define('ADMIN_ACCOUNT', 'administrator');
// the versioned access control files (copied to the location given by repos.properties when changed)
define('ACCOUNTS_FILE', ADMIN_ACCOUNT.'/trunk/admin/repos-users');
define('ACCESS_FILE', ADMIN_ACCOUNT.'/trunk/admin/repos-access');


?>