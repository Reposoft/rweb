
<?php

// stylesheet to be included by XSLT files that want to access repos.properties

function upOne($dirname) { return substr($dirname, 0, strrpos(rtrim(strtr($dirname,'\\','/'),'/'),'/') ); }
require( upOne(dirname(__FILE__)) . "/repos.properties.php" );

header('Content-type: text/xml');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns="http://www.w3.org/1999/xhtml">
<xsl:output standalone="no"/>
<?php

global $repos_config;
foreach ( $repos_config as $key => $value ) {
	echo "<xsl:param name=\"$key\" select=\"'$value'\"/>\n";
}

?>
</xsl:stylesheet>