<?php
/**
 *
 *
 * @package lib
 */

function convertGetCommand() {
	$installed = dirname(__FILE__).DIRECTORY_SEPARATOR.'installed'.DIRECTORY_SEPARATOR;
	if (file_exists($installed)) {
		return '"'.$installed.'convert"';
	}
	// expect imagemagick in path
	return 'convert';
	// GraphicsMagick: return 'gm convert';
}

?>
