<?php
/**
 * Executes as post-commit hook.
 * The output will not be read by anyone.
 *
 * Testurl: http://test.repos.se/repos/admin/post-commit/?repopath=/srv/www/vhosts/repos.se/test/repo&rev=1
 * 
 * @package admin
 * @deprecated use sdmin/hooks/ instead
 */
require('../../conf/Command.class.php');

$home = getConfig('home_path');
if (!$home) {
	trigger_error('home_path not set in configuration, can not execute hook scripts');
}

$path = $_GET['repopath'];
// TODO validate that repo is the configured
$rev = $_GET['rev'];
// TODO validate that rev is integer



$export = array(
	'administration/trunk/repos-access' => getConfig('admin_folder').getConfig('access_file')
);





?>
