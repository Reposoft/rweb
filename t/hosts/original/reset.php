<?php
// NOTE: This file exits after reset (see below), run setup.pl as a wrapper instead

// don't require anything special from CLI's php.ini

// settings for repos.properties.php
$_SERVER['DOCUMENT_ROOT'] = dirname(dirname(dirname(__FILE__))).'/hosts/original/html';
$_SERVER['REPOS_TEST_ALLOW_RESET'] = 'on';

$basedir = dirname(dirname(dirname(dirname(__FILE__))));
define('ReposWeb', $basedir.'/www/');

// do the setup, Report runs 'exit'
require($basedir.'/repos-test/reset/testrepo/index.php');

?>

