<?php
/**
 *
 *
 * @package
 */
require('../../reposweb.inc.php');
require(ReposWeb.'conf/repos.properties.php');

$webapp = getWebapp();
if (preg_match('/^\w+:\/\/[\w\d\.\-]+(\/.*)$/', $webapp, $matches)) {
	$webapp = $matches[1];
}

$q = getSelfQuery();

// make the test suite url relative to selenium
//$q = preg_replace('/(test|resultsUrl)=[.\/]*/','$1='.urlencode($webapp).'test/', $q);
$q = preg_replace('/(test|resultsUrl)=[.\/]*/','$1=/repos-test/', $q);

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Location: {$webapp}lib/selenium/core/TestRunner.html?$q");

?>
