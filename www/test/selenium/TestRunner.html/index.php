<?php
/**
 *
 *
 * @package
 */
require('../../../conf/repos.properties.php');

$webapp = getWebapp();
$q = getSelfQuery();

$q = preg_replace('/(test|resultsUrl)=[.\/]*/','$1=..%2F..%2F..%2Ftest', $q);

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Location: {$webapp}lib/selenium/core/TestRunner.html?$q");

?>
