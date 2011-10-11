<?php

$exclude = 'refresh/**,jquery.jqUploader/**';

require 'preg_find.php';

$required = preg_find('/\.js$/', '../../plugins', PREG_FIND_RECURSIVE);

$optional = preg_find('/\.load\.js$/', '../../../repos-plugins', PREG_FIND_RECURSIVE);

$scripts = array_merge($required, $optional);

$big = '../head.js.big';
// concatenate
$cat = fopen('../head.js.big', 'w');

foreach ($scripts as $file) {
	$s = fopen($file, 'r');
	while (!feof($s)) {
    	fwrite($cat, fread($s, 4096));
    }
}

require( 'jsmin-1.1.1.php' );

echo JSMin::minify(file_get_contents($big));

?>