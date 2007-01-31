<?php
/**
 * Batch operations on the ACL in the repository administration folder.
 * 
 * create=[username]
 * delete=[username]
 * 
 * @package account
 */

define('ACCESS_FILE', '/administration/repos-access.acl');

require('../../open/SvnOpenFile.class.php');

$file = new SvnOpenFile(ACCESS_FILE);

if ($file->getStatus()!=200) trigger_error('This account does not have access to the ACL', E_USER_ERROR);


?>