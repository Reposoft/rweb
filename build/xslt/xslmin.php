<?php
/**
 * XSLT file minimizer (c) 2007 Staffan Olsson
 * For PHP >=4.3.0
 */
 
if ( count( $argv ) == 2 ) {
	echo "{$argv[1]}: ";
	run($argv[1], $argv[1]);
} elseif ( count( $argv ) == 3 ) {
	echo "{$argv[1]}: ";
	run($argv[1], $argv[2]);
} else {
	unittest();
}

function run($source, $destination) {
	if (!file_exists($source)) die("Can not find source file $source");
	//if (!is_writable($destination)) die("Can not write to destination file $destination");
	$source = file_get_contents($source);
	$minimized = minimizeXslt($source);
	$f = fopen($destination, 'w');
	fwrite($f, $minimized);
	fclose($f);
	echo "was ".strlen($source)." bytes, wrote ".strlen($minimized)." bytes\n";
}

/**
 * Strinps comments and newlines from XSLT.
 * @param string $source xslt read from file
 * @param boolean $stripNewlines set to false to preserve newlines and the spaces around them,
 * 	currently this script deletes newlines inside CDATA sections too.
 * @return the minimized xsl
 */
function minimizeXslt($source, $stripNewlines=true) {
	if (!$stripNewlines) die('stripNewlines=false not supported yet');
	$source = preg_replace("/<!--.*-->\r?\n?/msU", "", $source);
	$source = preg_replace("/\s*\r?\n\s*/", " ", $source);
	$source = preg_replace("/\s+$/", "", $source);
	return $source;
}

function unittest() {
	echo "XSLT minimizer by Staffan Olsson\n";
	echo "Command line usage: (php) xslmin.php /source/path [/destination/path]\n";
	echo "If destination path is omitted, result will be written to source path";
	echo "\n--- unit tests ---\n";
	
	$t1 = '<?xml version="1.0">
	<!--my xsl-->
	<xsl:stylesheet>
	';
	assertEquals('<?xml version="1.0"> <xsl:stylesheet>', minimizeXslt($t1));
	
	$t2 = '<?xml >
	<!-- my
	short
	xml --><xsl:stylesheet>
	<!-- start --> ';
	assertEquals('<?xml > <xsl:stylesheet>', minimizeXslt($t2));
	
	$t3 = '<xsl:stylesheet
	version="1.0">';
	assertEquals('<xsl:stylesheet version="1.0">', minimizeXslt($t3));
	
	$t4 = '<p><xsl:text>A</xsl:text>  <xsl:text>text</xsl:text></p>';
	assertEquals($t4, minimizeXslt($t4));
}

function assertEquals($string1, $string2) {
	if ($string1 == $string2) {
		echo "PASS\n";
		return 0;
	}
	echo "FAIL [$string1] != [$string2]\n";
	return 1;
}

?>
