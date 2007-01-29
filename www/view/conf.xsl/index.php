<?php
/**
DEPRECATED This concept is not used, because imports are not supported by Safari (2.0.3).
*/

header('Content-type: text/xml; charset=UTF-8');
require(dirname(dirname(dirname(__FILE__))).'/conf/repos.properties.php');
?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output standalone="no"/>
<?php
$conf = array(
	'repos_web' => getConfig('repos_web'),
	'repo_url' => getConfig('repo_url'),
	'lang' => getUserLocale(),
	'theme' => getUserTheme()
);
foreach($conf as $key => $val) {
	echo("<xsl:variable name=\"$key\">$val</xsl:variable>\n");
}
?>
</xsl:stylesheet>
