<?php
/**
 *
 *
 * @package
 */
require('../../../conf/repos.properties.php');

$webapp = getWebapp();
if (preg_match('/^\w+:\/\/[\w\d\.\-]+(\/.*)$/', $webapp, $matches)) {
	$webapp = $matches[1];
}

$q = getSelfQuery();

$q = preg_replace('/(test|resultsUrl)=[.\/]*/','$1='.urlencode($webapp).'test', $q);

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Location: {$webapp}lib/selenium/core/TestRunner.html?$q");

?>
